const express = require("express");
const flash = require("connect-flash");
const session = require("express-session");
const uuid = require("uuid").v4;
const crypto = require("crypto");
const bot = require("./bot");

const BIND_ADDR = process.env.BIND_ADDR || "0.0.0.0";
const LPORT = process.env.BOTPORT || 4000;
const SESSION_SECRET = process.env.SESSION_SECRET || uuid();

const app = express();

// body parser
app.use(express.urlencoded({ extended: false }));

app.set("view engine", "ejs");
app.use(express.static(__dirname + "/public"));

app.use(
    session({
        secret: SESSION_SECRET,
        resave: false,
        saveUninitialized: false,
        cookie: {
            httpOnly: true,
        },
    })
);

// connect flash
app.use(flash());
app.use((req, res, next) => {
    res.locals.message = req.flash();
    next();
});

function isValidHttpUrl(string) {
    let url;
    try {
        url = new URL(string);
    } catch (_) {
        return false;
    }

    return url.protocol === "http:" || url.protocol === "https:";
}

function genCaptcha() {
    function randomInteger(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
    let ranNum = randomInteger(5000000, 1000000000);
    console.log(ranNum);
    let md5 = crypto.createHash("md5");
    return md5.update(ranNum.toString()).digest("hex").slice(0, 7);
}

app.get("/", function (req, res) {
    let captcha = genCaptcha();
    req.session.captcha = captcha;
    return res.render("index", { captcha: captcha });
});

app.post("/", async (req, res) => {
    let { captcha, url } = req.body;
    let md5 = crypto.createHash("md5");
    if (md5.update(captcha).digest("hex").slice(0, 7) !== req.session.captcha)
        return res.status(403).send({ error: "Your captcha is wrong" });
    req.session.captcha = "#";
    if (typeof url !== "string" || isValidHttpUrl(url) === false) {
        return res.status(403).send({ error: "Your url is wrong" });
    }
    await bot.visit(url);
    res.send({ msg: "The bot has visited your url." });
});

app.listen(
    LPORT,
    BIND_ADDR,
    console.log(`Started on http://${BIND_ADDR}:${LPORT}`)
);
