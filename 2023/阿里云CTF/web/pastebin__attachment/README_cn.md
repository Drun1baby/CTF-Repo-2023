# README

## 怎么构建

```bash
cd files/app/ && docker build -t web . 
cd ../../files/bot/ && docker build -t bot .
docker run -itd -p 1337:4000 --name web web
docker run -itd -p 1338:4000 --link web:web --name bot bot
```



## 注意：请弄明白你到底在做什么

在尝试提交你的 POC 到题目前，请尽量在本地测试！

题目有问题请在钉钉群联系



## Patch

修复了一个在 `bot/src/bot.js` 中导致 bot 崩溃的错误，抱歉带来的不便。