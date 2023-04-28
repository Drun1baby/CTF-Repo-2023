package com.ctf.ezser.utils;

import java.io.*;

public class MyObjectInputStream extends ObjectInputStream {

   private static final String[] blacklist = new String[]{
           "java\\.security.*", "java\\.rmi.*",  "com\\.fasterxml.*", "com\\.ctf\\.*",
           "org\\.springframework.*", "org\\.yaml.*", "javax\\.management\\.remote.*"
   };

   public MyObjectInputStream(InputStream inputStream) throws IOException {
      super(inputStream);
   }

   protected Class resolveClass(ObjectStreamClass cls) throws IOException, ClassNotFoundException {
      if(!contains(cls.getName())) {
         return super.resolveClass(cls);
      } else {
         throw new InvalidClassException("Unexpected serialized class", cls.getName());
      }
   }

   public static boolean contains(String targetValue) {
      for (String forbiddenPackage : blacklist) {
         if (targetValue.matches(forbiddenPackage))
            return true;
      }
      return false;
   }
}
