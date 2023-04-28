const express = require("express");
const expressLayouts = require("express-ejs-layouts");
const mongoose = require("mongoose");
const uuid = require("uuid").v4;
const flash = require("connect-flash");
const session = require("express-session");
const cookieParser = require("cookie-parser");
const MongoStore = require("connect-mongo");
const compression = require("compression");

const BIND_ADDR = process.env.BIND_ADDR || "127.0.0.1";
const LPORT = process.env.LPORT || 3000;
const SESSION_SECRET = uuid();

const app = express();

// DB config
const db = require("./helper/db").MongoURI;
mongoose
    .connect(db)
    .then(() => console.log("MongoDB Connected."))
    .catch((err) => console.log(err));

// EJS
app.use(expressLayouts);
app.set("view engine", "ejs");
app.set("etag", false);

// body parser
app.use(express.urlencoded({ extended: false }));

// cookie parser
app.use(cookieParser());

// trust first proxy for secure cookies
app.set("trust proxy", 1);

// compression
app.use(compression());

// express session
app.use(
    session({
        store: MongoStore.create({
            mongoUrl: db,
            autoRemove: "interval",
            autoRemoveInterval: 10, // In minutes. Default
        }),
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

// csp
app.use((req, res, next) => {
    res.header(
        "Content-Security-Policy",
        "default-src 'self'; script-src 'self';style-src 'self' https://cdn.bootcdn.net/; object-src 'none';"
    );
    next();
});

// routes
app.use("/", require("./routes/index"));
app.use(express.static(__dirname + "/public"));
app.use("/user", require("./routes/user"));
app.use("/admin", require("./routes/admin"));

app.listen(
    LPORT,
    BIND_ADDR,
    console.log(`Started on http://${BIND_ADDR}:${LPORT}`)
);
