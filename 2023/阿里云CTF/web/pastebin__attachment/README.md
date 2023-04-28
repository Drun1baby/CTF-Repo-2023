# README

## How to build

```bash
cd files/app/ && docker build -t web . 
cd ../../files/bot/ && docker build -t bot .
docker run -itd -p 1337:4000 --name web web
docker run -itd -p 1338:4000 --link web:web --name bot bot
```



## NOTE: PLZ KNOW WHAT YOU DO IN YOUR POC

Test your POC in your local env before you try to exploit the remote.





## Patch

Fix an bot carsh error in `bot/src/bot.js` . Sorry for any trouble.