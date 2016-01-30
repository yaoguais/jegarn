package com.jegarn.minions.utils;

import android.os.Environment;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

public class SdUtil {
    private String SDPATH;
    private int FILESIZE = 1;

    public String getSDPATH() {
        return SDPATH;
    }

    public SdUtil() {
        if (android.os.Environment.getExternalStorageState().equals(android.os.Environment.MEDIA_MOUNTED)) {
            SDPATH = Environment.getExternalStorageDirectory() + "/";
        }
    }

    public File creatSDFile(String fileName) throws IOException {
        File file = new File(SDPATH + fileName);
        file.createNewFile();
        return file;
    }

    public void delFolder(String folderPath) {
        try {
            delAllFile(folderPath);
            String filePath = folderPath;
            filePath = filePath.toString();
            java.io.File myFilePath = new java.io.File(filePath);
            myFilePath.delete();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void delAllFile(String path) {
        File file = new File(path);
        if (!file.exists()) {
            return;
        }
        if (!file.isDirectory()) {
            return;
        }
        String[] tempList = file.list();
        File temp;
        for (int i = 0; i < tempList.length; i++) {
            if (path.endsWith(File.separator)) {
                temp = new File(path + tempList[i]);
            } else {
                temp = new File(path + File.separator + tempList[i]);
            }
            if (temp.isFile()) {
                temp.delete();
            }
            if (temp.isDirectory()) {
                delAllFile(path + "/" + tempList[i]);
                delFolder(path + "/" + tempList[i]);
            }
        }
    }

    public File creatSDDir(String dirName) {
        File dir = new File(SDPATH + dirName);
        dir.mkdir();
        return dir;
    }

    public boolean isFileExist(String fileName) {
        File file = new File(SDPATH + fileName);
        return file.exists();
    }

    public long file_time(String fileName) {
        File file = new File(SDPATH + fileName);
        return file.lastModified();
    }

    public ByteArrayOutputStream readSdFile(String path, String filename) {
        FileInputStream is;
        try {
            is = new FileInputStream(SDPATH + path + filename);
        } catch (FileNotFoundException e) {
            e.printStackTrace();
            return null;
        }
        ByteArrayOutputStream bos = new ByteArrayOutputStream();
        byte[] array = new byte[1024];
        int len;
        try {
            while ((len = is.read(array)) != -1) {
                bos.write(array, 0, len);
            }
            bos.close();
            is.close();
        } catch (IOException e) {
            e.printStackTrace();
            return null;
        }
        return bos;
    }

    public File write2SDFromInput(String path, String fileName, InputStream input) {
        File file = null;
        OutputStream output = null;
        try {
            creatSDDir(path);
            file = creatSDFile(path + fileName);
            output = new FileOutputStream(file);
            byte[] buffer = new byte[FILESIZE];
            while ((input.read(buffer)) != -1) {
                output.write(buffer);
            }
            output.flush();
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            try {
                System.out.println("" + file.getAbsolutePath());
                output.close();
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
        return file;
    }

    public static void deleteFile(String fileStr) {
        File file = new File(fileStr);
        if (file.exists()) { // 判断文件是否存在
            if (file.isFile()) { // 判断是否是文件
                file.delete(); // delete()方法 你应该知道 是删除的意思;
            }
            file.delete();
        }
    }

    public void delFile(String path) {
        String str = SDPATH + path;
        File file = new File(str);
        if (file.exists()) {
            file.delete();
        }
    }

    public int CopyFileToFile(File fromFile, File toFile) {

        try {
            InputStream fosfrom = new FileInputStream(fromFile);
            OutputStream fosto = new FileOutputStream(toFile);
            byte bt[] = new byte[1024];
            int c;
            while ((c = fosfrom.read(bt)) > 0) {
                fosto.write(bt, 0, c);
            }
            fosfrom.close();
            fosto.close();
            return 0;

        } catch (Exception ex) {
            return -1;
        }
    }

    public File CopyFileRomToSD(String romfile) {
        if (!android.os.Environment.getExternalStorageState().equals(android.os.Environment.MEDIA_MOUNTED)) {
            return null;

        }

        File rom = new File(romfile);
        File sd = new File(Environment.getExternalStorageDirectory() + romfile);

        if (CopyFileToFile(rom, sd) == 0) {
            return sd;
        }

        return null;
    }
}