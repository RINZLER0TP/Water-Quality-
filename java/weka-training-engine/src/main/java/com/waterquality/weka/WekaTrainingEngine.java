package com.waterquality.weka;

import weka.classifiers.Classifier;
import weka.classifiers.Evaluation;
import weka.classifiers.bayes.NaiveBayes;
import weka.classifiers.functions.Logistic;
import weka.classifiers.meta.FilteredClassifier;
import weka.classifiers.rules.OneR;
import weka.classifiers.rules.ZeroR;
import weka.core.Instances;
import weka.core.SerializationHelper;
import weka.core.converters.CSVLoader;
import weka.filters.Filter;
import weka.filters.MultiFilter;
import weka.filters.unsupervised.attribute.RemoveUseless;
import weka.filters.unsupervised.attribute.ReplaceMissingValues;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.Map;
import java.util.Random;

public class WekaTrainingEngine {

    public static void main(String[] args) {
        Map<String, String> options = parseArgs(args);

        try {
            String csvPath = required(options, "csv");
            String targetColumn = required(options, "target");
            String algorithm = required(options, "algorithm");
            String modelPath = required(options, "model");
            String logPath = required(options, "log");
            int folds = Integer.parseInt(options.getOrDefault("folds", "10"));
            int seed = Integer.parseInt(options.getOrDefault("seed", "42"));

            long startedAt = System.currentTimeMillis();

            CSVLoader loader = new CSVLoader();
            loader.setSource(new File(csvPath));
            Instances data = loader.getDataSet();

            if (data.numAttributes() == 0) {
                throw new IllegalStateException("El CSV no contiene atributos válidos.");
            }

            int classIndex = attributeIndexByName(data, targetColumn);
            if (classIndex < 0) {
                throw new IllegalStateException("No se encontró la columna objetivo: " + targetColumn);
            }

            data.setClassIndex(classIndex);

            Classifier classifier = buildClassifier(algorithm);
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
        } catch (Exception exception) {
            String errorJson = buildErrorJson(exception.getMessage());
            try {
                writeLog(options.getOrDefault("log", ""), errorJson);
            } catch (IOException ignored) {
            }
            System.err.println(errorJson);
            System.exit(1);
        }
    }

    private static Classifier buildClassifier(String algorithm) {
        return switch (algorithm.toLowerCase()) {
            case "zeror" -> new ZeroR();
            case "oner" -> new OneR();
            case "naive_bayes" -> new NaiveBayes();
            case "logistic" -> new Logistic();
            default -> throw new IllegalStateException("Algoritmo no soportado: " + algorithm);
        };
    }

    private static int attributeIndexByName(Instances data, String attributeName) {
        for (int index = 0; index < data.numAttributes(); index++) {
            if (data.attribute(index).name().equals(attributeName)) {
                return index;
            }
        }

        return -1;
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

    private static String buildErrorJson(String message) {
        return "{\"success\":false,\"error\":" + jsonString(message == null ? "Error inesperado" : message) + "}";
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
