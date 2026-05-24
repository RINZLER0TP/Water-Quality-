package com.waterquality.weka;

import weka.classifiers.Classifier;
import weka.classifiers.Evaluation;
import weka.classifiers.bayes.NaiveBayes;
import weka.classifiers.functions.Logistic;
import weka.classifiers.meta.FilteredClassifier;
import weka.classifiers.rules.OneR;
import weka.classifiers.rules.ZeroR;
import weka.core.Attribute;
import weka.core.Instance;
import weka.core.DenseInstance;
import weka.core.Instances;
import weka.core.SerializationHelper;
import weka.core.converters.CSVLoader;
import weka.filters.Filter;
import weka.filters.MultiFilter;
import weka.filters.unsupervised.attribute.RemoveUseless;
import weka.filters.unsupervised.attribute.ReplaceMissingValues;
import weka.filters.unsupervised.attribute.StringToNominal;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashSet;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.Random;

public class WekaTrainingEngine {

    public static void main(String[] args) {
        Map<String, String> options = parseArgs(args);

        try {
            String mode = options.getOrDefault("mode", "train");

            if ("predict".equalsIgnoreCase(mode)) {
                handlePredict(options);
            } else {
                handleTrain(options);
            }
        } catch (Exception exception) {
            String errorJson = buildErrorJson(exception);
            try {
                writeLog(options.getOrDefault("log", ""), errorJson);
            } catch (IOException ignored) {
            }
            System.err.println(errorJson);
            System.exit(1);
        }
    }

    private static void handleTrain(Map<String, String> options) throws Exception {
        String csvPath = required(options, "csv");
        String targetColumn = required(options, "target");
        String algorithm = required(options, "algorithm");
        String modelPath = required(options, "model");
        String logPath = options.getOrDefault("log", "");
        int folds = Integer.parseInt(options.getOrDefault("folds", "10"));
        int seed = Integer.parseInt(options.getOrDefault("seed", "42"));

        long startedAt = System.currentTimeMillis();

        Instances data;
        if ("stdin".equalsIgnoreCase(csvPath) || "-".equals(csvPath)) {
            String csvContent = new String(System.in.readAllBytes(), StandardCharsets.UTF_8);

            if (csvContent.isBlank()) {
                throw new IllegalStateException("CSV vacío recibido por stdin; el proceso Java no recibió datos de entrada.");
            }

            data = loadCsvFromText(csvContent);
        } else {
            File csvFile = normalizePath(csvPath);
            data = loadCsvWithRetry(csvFile, 5, 250L);
        }

        if (data.numAttributes() == 0) {
            throw new IllegalStateException("El CSV no contiene atributos válidos.");
        }

        folds = Math.min(folds, data.numInstances());
        if (folds < 2) {
            throw new IllegalStateException("Se necesitan al menos 2 instancias para ejecutar validación cruzada.");
        }

        int classIndex = attributeIndexByName(data, targetColumn);
        if (classIndex < 0) {
            throw new IllegalStateException("No se encontró la columna objetivo: " + targetColumn);
        }

        data.setClassIndex(classIndex);

        StringToNominal stringToNominal = new StringToNominal();
        stringToNominal.setAttributeRange("first-last");
        stringToNominal.setInputFormat(data);
        data = Filter.useFilter(data, stringToNominal);

        Classifier classifier = buildClassifier(algorithm, options);
        
        MultiFilter filter = new MultiFilter();
        filter.setFilters(new Filter[]{new ReplaceMissingValues(), new RemoveUseless()});

        FilteredClassifier filteredClassifier = new FilteredClassifier();
        filteredClassifier.setFilter(filter);
        filteredClassifier.setClassifier(classifier);

        Evaluation evaluation = new Evaluation(data);
        evaluation.crossValidateModel(filteredClassifier, data, folds, new Random(seed));

        filteredClassifier.buildClassifier(data);

        File modelFile = new File(modelPath);
        File parent = modelFile.getParentFile();
        if (parent != null && !parent.exists()) {
            parent.mkdirs();
        }

        SerializationHelper.write(modelPath, filteredClassifier);

        long trainingTimeMs = System.currentTimeMillis() - startedAt;

        String json = buildSuccessJson(
                algorithm,
                csvPath,
                targetColumn,
                modelPath,
                evaluation,
                folds,
                seed,
                trainingTimeMs,
                data.numInstances(),
                data.numAttributes()
        );

        writeLog(logPath, json);
        System.out.println(json);
    }

    private static void handlePredict(Map<String, String> options) throws Exception {
        String modelPath = required(options, "model");
        String csvPath = required(options, "csv"); // CSV con los datos a predecir
        String logPath = options.getOrDefault("log", "");

        long startedAt = System.currentTimeMillis();

        // Cargar el modelo pre-entrenado
        Classifier classifier = (Classifier) SerializationHelper.read(modelPath);

        // Cargar los datos de prueba
        CSVLoader loader = new CSVLoader();
        loader.setSource(new File(csvPath));
        Instances data = loader.getDataSet();

        if (data.numInstances() == 0) {
            throw new IllegalStateException("El CSV no contiene instancias para predecir.");
        }

        // Asumimos que la última columna es la clase objetivo (como en el entrenamiento)
        // O podemos buscarla si es enviada. En Weka es obligatorio tener el atributo clase,
        // aunque su valor sea '?'.
        String targetColumn = options.getOrDefault("target", "Potability");
        int classIndex = attributeIndexByName(data, targetColumn);
        if (classIndex < 0) {
            // Si el CSV de predicción no incluye la columna Potability, Weka fallará por 
            // incompatibilidad de cabeceras. El CSV generado DEBE incluir la columna objetivo.
            throw new IllegalStateException("No se encontró la columna objetivo en el CSV de predicción: " + targetColumn);
        }
        data.setClassIndex(classIndex);

        StringToNominal stringToNominal = new StringToNominal();
        stringToNominal.setAttributeRange("first-last");
        stringToNominal.setInputFormat(data);
        data = Filter.useFilter(data, stringToNominal);

        // Tomar la primera instancia
        Instance instance = data.instance(0);

        // Realizar predicción
        double predictedClass = classifier.classifyInstance(instance);
        String predictedClassName = data.classAttribute().value((int) predictedClass);
        
        // Obtener distribución de probabilidad
        double[] distribution = classifier.distributionForInstance(instance);
        double confidence = distribution[(int) predictedClass];

        long executionTimeMs = System.currentTimeMillis() - startedAt;

        String json = buildPredictSuccessJson(predictedClassName, confidence, executionTimeMs);

        writeLog(logPath, json);
        System.out.println(json);
    }

    private static Classifier buildClassifier(String algorithm, Map<String, String> options) {
        return switch (algorithm.toLowerCase()) {
            case "zeror" -> new ZeroR();
            case "oner" -> buildOneR(options);
            case "naive_bayes" -> buildNaiveBayes(options);
            case "logistic" -> buildLogistic(options);
            default -> throw new IllegalStateException("Algoritmo no soportado: " + algorithm);
        };
    }

    private static OneR buildOneR(Map<String, String> options) {
        OneR classifier = new OneR();
        int bucketSize = integerOption(options, "bucket-size", 6);
        classifier.setMinBucketSize(Math.max(1, bucketSize));
        return classifier;
    }

    private static NaiveBayes buildNaiveBayes(Map<String, String> options) {
        NaiveBayes classifier = new NaiveBayes();
        classifier.setUseKernelEstimator(booleanOption(options, "use-kernel-estimator", false));
        classifier.setUseSupervisedDiscretization(booleanOption(options, "use-supervised-discretization", false));
        return classifier;
    }

    private static Logistic buildLogistic(Map<String, String> options) {
        Logistic classifier = new Logistic();
        classifier.setRidge(doubleOption(options, "ridge", 0.00000001));
        classifier.setMaxIts(integerOption(options, "max-iterations", 500));
        return classifier;
    }

    private static boolean booleanOption(Map<String, String> options, String key, boolean defaultValue) {
        String value = options.get(key);
        if (value == null || value.isBlank()) {
            return defaultValue;
        }

        return "true".equalsIgnoreCase(value) || "1".equals(value) || "yes".equalsIgnoreCase(value);
    }

    private static int integerOption(Map<String, String> options, String key, int defaultValue) {
        String value = options.get(key);
        if (value == null || value.isBlank()) {
            return defaultValue;
        }

        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException exception) {
            return defaultValue;
        }
    }

    private static double doubleOption(Map<String, String> options, String key, double defaultValue) {
        String value = options.get(key);
        if (value == null || value.isBlank()) {
            return defaultValue;
        }

        try {
            return Double.parseDouble(value);
        } catch (NumberFormatException exception) {
            return defaultValue;
        }
    }

    private static int attributeIndexByName(Instances data, String attributeName) {
        for (int index = 0; index < data.numAttributes(); index++) {
            if (data.attribute(index).name().equals(attributeName)) {
                return index;
            }
        }

        return -1;
    }

    private static String buildPredictSuccessJson(String predictedClass, double confidence, long executionTimeMs) {
        return "{" +
                "\"success\":true," +
                "\"prediction\":{" +
                "\"class\":" + jsonString(predictedClass) + "," +
                "\"confidence\":" + doubleValue(confidence) + "," +
                "\"execution_time_ms\":" + executionTimeMs +
                "}" +
                "}";
    }

    private static String buildSuccessJson(
            String algorithm,
            String csvPath,
            String targetColumn,
            String modelPath,
            Evaluation evaluation,
            int folds,
            int seed,
            long trainingTimeMs,
            int rows,
            int columns
    ) {
        StringBuilder builder = new StringBuilder();
        builder.append('{');
        builder.append("\"success\":true,");
        builder.append("\"algorithm\":").append(jsonString(algorithm)).append(',');
        builder.append("\"dataset\":{");
        builder.append("\"csv_path\":").append(jsonString(csvPath)).append(',');
        builder.append("\"target_column\":").append(jsonString(targetColumn)).append(',');
        builder.append("\"rows\":").append(rows).append(',');
        builder.append("\"columns\":").append(columns);
        builder.append('}').append(',');
        builder.append("\"metrics\":{");
        builder.append("\"accuracy\":").append(doubleValue(evaluation.pctCorrect() / 100.0)).append(',');
        builder.append("\"precision\":").append(doubleValue(evaluation.weightedPrecision())).append(',');
        builder.append("\"recall\":").append(doubleValue(evaluation.weightedRecall())).append(',');
        builder.append("\"f1_score\":").append(doubleValue(evaluation.weightedFMeasure())).append(',');
        builder.append("\"kappa\":").append(doubleValue(evaluation.kappa())).append(',');
        builder.append("\"training_time_ms\":").append(trainingTimeMs).append(',');
        builder.append("\"cross_validation_folds\":").append(folds).append(',');
        builder.append("\"random_seed\":").append(seed);
        builder.append('}').append(',');
        builder.append("\"confusion_matrix\":").append(jsonMatrix(evaluation.confusionMatrix())).append(',');
        builder.append("\"model_path\":").append(jsonString(modelPath));
        builder.append('}');
        return builder.toString();
    }

    private static String buildErrorJson(Exception exception) {
        StringBuilder sb = new StringBuilder();
        sb.append(exception.getClass().getSimpleName()).append(": ").append(exception.getMessage()).append(" - ");
        for (StackTraceElement elem : exception.getStackTrace()) {
            if (elem.getClassName().contains("waterquality")) {
                sb.append(elem.getFileName()).append(":").append(elem.getLineNumber()).append(" ");
            }
        }
        return "{\"success\":false,\"error\":" + jsonString(sb.toString()) + "}";
    }

    private static Map<String, String> parseArgs(String[] args) {
        Map<String, String> options = new LinkedHashMap<>();
        Arrays.stream(args).forEach(argument -> {
            if (argument.startsWith("--") && argument.contains("=")) {
                String[] parts = argument.substring(2).split("=", 2);
                options.put(parts[0], parts.length > 1 ? parts[1] : "");
            }
        });

        return options;
    }

    private static String required(Map<String, String> options, String key) {
        String value = options.get(key);
        if (value == null || value.isBlank()) {
            throw new IllegalStateException("Falta el parámetro obligatorio --" + key);
        }

        return value;
    }

    private static void writeLog(String logPath, String content) throws IOException {
        if (logPath == null || logPath.isBlank()) {
            return;
        }

        File logFile = new File(logPath);
        File parent = logFile.getParentFile();
        if (parent != null && !parent.exists()) {
            parent.mkdirs();
        }

        try (FileWriter writer = new FileWriter(logFile, StandardCharsets.UTF_8, false)) {
            writer.write(content);
        }
    }

    private static String jsonString(String value) {
        return '"' + value
                .replace("\\", "\\\\")
                .replace("\"", "\\\"")
                .replace("\n", "\\n")
                .replace("\r", "\\r")
                .replace("\t", "\\t") + '"';
    }

    private static String doubleValue(double value) {
        if (Double.isNaN(value) || Double.isInfinite(value)) {
            return "0.0";
        }

        return String.valueOf(value);
    }

    private static File normalizePath(String path) throws IOException {
        Path normalizedPath = Paths.get(path).toAbsolutePath().normalize();
        return normalizedPath.toFile();
    }

    private static Instances loadCsvFromText(String csvContent) throws Exception {
        List<String> lines = Arrays.stream(csvContent.split("\\R"))
                .map(String::trim)
                .filter(line -> !line.isEmpty())
                .toList();

        if (lines.isEmpty()) {
            throw new IllegalStateException("El CSV enviado por stdin está vacío.");
        }

        String delimiter = String.valueOf(detectDelimiter(lines.get(0)));
        List<String> headers = splitCsvLine(lines.get(0), delimiter.charAt(0));

        if (headers.isEmpty()) {
            throw new IllegalStateException("No se pudieron leer los encabezados del CSV.");
        }

        List<List<String>> rows = new ArrayList<>();
        for (int lineIndex = 1; lineIndex < lines.size(); lineIndex++) {
            List<String> row = splitCsvLine(lines.get(lineIndex), delimiter.charAt(0));
            if (!row.isEmpty()) {
                rows.add(row);
            }
        }

        if (rows.isEmpty()) {
            throw new IllegalStateException("El CSV no contiene filas de datos.");
        }

        List<Attribute> attributes = new ArrayList<>();
        for (int columnIndex = 0; columnIndex < headers.size(); columnIndex++) {
            String header = headers.get(columnIndex);
            boolean numeric = true;
            Set<String> nominalValues = new LinkedHashSet<>();

            for (List<String> row : rows) {
                String value = columnIndex < row.size() ? row.get(columnIndex).trim() : "";

                if (value.isEmpty()) {
                    continue;
                }

                nominalValues.add(value);

                if (numeric) {
                    try {
                        Double.parseDouble(value);
                    } catch (NumberFormatException exception) {
                        numeric = false;
                    }
                }
            }

            if (numeric) {
                attributes.add(new Attribute(header));
            } else {
                attributes.add(new Attribute(header, new ArrayList<>(nominalValues)));
            }
        }

        Instances data = new Instances("csv_dataset", new ArrayList<>(attributes), rows.size());

        for (List<String> row : rows) {
            Instance instance = new DenseInstance(attributes.size());
            instance.setDataset(data);

            for (int columnIndex = 0; columnIndex < attributes.size(); columnIndex++) {
                Attribute attribute = attributes.get(columnIndex);
                String value = columnIndex < row.size() ? row.get(columnIndex).trim() : "";

                if (value.isEmpty()) {
                    instance.setMissing(attribute);
                    continue;
                }

                if (attribute.isNumeric()) {
                    instance.setValue(attribute, Double.parseDouble(value));
                } else {
                    instance.setValue(attribute, value);
                }
            }

            data.add(instance);
        }

        return data;
    }

    private static List<String> splitCsvLine(String line, char delimiter) {
        List<String> values = new ArrayList<>();
        StringBuilder current = new StringBuilder();
        boolean inQuotes = false;

        for (int index = 0; index < line.length(); index++) {
            char character = line.charAt(index);

            if (character == '"') {
                inQuotes = !inQuotes;
                continue;
            }

            if (character == delimiter && !inQuotes) {
                values.add(current.toString().trim());
                current.setLength(0);
                continue;
            }

            current.append(character);
        }

        values.add(current.toString().trim());
        return values;
    }

    private static char detectDelimiter(String line) {
        Map<Character, Integer> candidates = new LinkedHashMap<>();
        candidates.put(',', countOccurrences(line, ','));
        candidates.put(';', countOccurrences(line, ';'));
        candidates.put('\t', countOccurrences(line, '\t'));

        char bestDelimiter = ',';
        int bestScore = -1;

        for (Map.Entry<Character, Integer> entry : candidates.entrySet()) {
            if (entry.getValue() > bestScore) {
                bestDelimiter = entry.getKey();
                bestScore = entry.getValue();
            }
        }

        return bestDelimiter;
    }

    private static int countOccurrences(String line, char character) {
        int count = 0;

        for (int index = 0; index < line.length(); index++) {
            if (line.charAt(index) == character) {
                count++;
            }
        }

        return count;
    }

    private static Instances loadCsvWithRetry(File csvFile, int attempts, long delayMs) throws Exception {
        Exception lastException = null;

        for (int attempt = 1; attempt <= attempts; attempt++) {
            CSVLoader loader = new CSVLoader();
            loader.setSource(csvFile);

            try {
                return loader.getDataSet();
            } catch (Exception exception) {
                lastException = exception;

                if (!isLikelyAccessDenied(exception) || attempt == attempts) {
                    throw new Exception("Error leyendo CSV en " + csvFile.getAbsolutePath() + " - " + exception.getMessage(), exception);
                }

                try {
                    Thread.sleep(delayMs * attempt);
                } catch (InterruptedException interruptedException) {
                    Thread.currentThread().interrupt();
                    throw new Exception("Error leyendo CSV en " + csvFile.getAbsolutePath() + " - lectura interrumpida.", interruptedException);
                }
            }
        }

        throw new Exception("Error leyendo CSV en " + csvFile.getAbsolutePath() + " - " + (lastException != null ? lastException.getMessage() : "desconocido"), lastException);
    }

    private static boolean isLikelyAccessDenied(Exception exception) {
        String message = exception.getMessage();

        if (message == null) {
            return false;
        }

        String normalizedMessage = message.toLowerCase();
        return normalizedMessage.contains("access denied") || normalizedMessage.contains("acceso denegado");
    }

    private static String jsonMatrix(double[][] matrix) {
        StringBuilder builder = new StringBuilder();
        builder.append('[');

        for (int row = 0; row < matrix.length; row++) {
            if (row > 0) {
                builder.append(',');
            }

            builder.append('[');
            for (int column = 0; column < matrix[row].length; column++) {
                if (column > 0) {
                    builder.append(',');
                }
                builder.append(doubleValue(matrix[row][column]));
            }
            builder.append(']');
        }

        builder.append(']');
        return builder.toString();
    }
}
