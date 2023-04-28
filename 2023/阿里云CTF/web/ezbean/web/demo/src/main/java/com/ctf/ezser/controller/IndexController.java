package com.ctf.ezser.controller;

import com.ctf.ezser.utils.MyObjectInputStream;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.io.ByteArrayInputStream;
import java.util.Base64;

@RestController
public class IndexController {
        @RequestMapping("/read")
        public String read(@RequestParam String data) {
            try {
                byte[] bytes = Base64.getDecoder().decode(data);
                ByteArrayInputStream byteArrayInputStream = new ByteArrayInputStream(bytes);
                MyObjectInputStream objectInputStream = new MyObjectInputStream(byteArrayInputStream);
                objectInputStream.readObject();
            } catch (Exception e) {
                e.printStackTrace();
                return "error";
            }
            return "success";
        }
}
