package com.ctf.ezser.bean;

import java.io.IOException;
import java.io.Serializable;
import javax.management.remote.JMXConnector;

public class MyBean implements Serializable {

   private Object url;
   private Object message;
   private JMXConnector conn;


   public MyBean() {}

   public MyBean(Object url, Object message) {
      this.url = url;
      this.message = message;
   }

   public MyBean(Object url, Object message, JMXConnector conn) {
      this.url = url;
      this.message = message;
      this.conn = conn;
   }

   public String getConnect() throws IOException {
      try {
         this.conn.connect();
         return "success";
      } catch (IOException var2) {
         return "fail";
      }
   }

   public void connect() {}

   public Object getMessage() {
      return this.message;
   }

   public void setMessage(Object message) {
      this.message = message;
   }

   public Object getUrl() {
      return this.url;
   }

   public void setUrl(Object url) {
      this.url = url;
   }
}
