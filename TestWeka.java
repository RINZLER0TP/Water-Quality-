import java.io.File;
import weka.core.converters.CSVLoader;

public class TestWeka {
    public static void main(String[] args) {
        try {
            CSVLoader loader = new CSVLoader();
            loader.setSource(new File("stdin"));
            loader.getDataSet();
        } catch (Exception e) {
            e.printStackTrace();
            System.out.println("Message: " + e.getMessage());
        }
    }
}
