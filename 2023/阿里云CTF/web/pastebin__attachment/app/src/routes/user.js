const express = require("express");
const ensureAuthenticated = require("../helper/auth").ensureAuthenticated;
const router = express.Router();
const User = require("../models/User");
const init = require("../helper/init");
const crypto = require("crypto");

init.initdb();

// login page
router.get("/login", async (req, res) => {
    return res.render("login");
});

//logout page
router.get("/logout", async (req, res) => {
    await req.session.destroy();
    res.cookie("login", "0", {
        maxAge: 900000,
        httpOnly: true,
    });
    res.clearCookie("connect.sid", {
        path: "/",
    })
        .status(200)
        .redirect("/user/login");
});

// register
router.get("/register", (req, res) => {
    return res.render("register");
});

// register post
router.post("/register", async (req, res) => {
    let { username, password, password2 } = req.body;
    const regexp = /^[a-z0-9]{4,32}$/gi;
    let errors = [];
    // check it
    if (!username || !password || !password2) {
        errors.push({ msg: "Please fill in all fields." });
    }
    if (password !== password2) {
        errors.push({ msg: "Password do not match." });
    }
    if (password.length < 4 || password.length > 32) {
        errors.push({
            msg: "Password must be at least 4 characters and no more than 32 characters.",
        });
    }
    if (username.length < 4 || password.length > 32) {
        errors.push({
            msg: "Username must be at least 4 characters and no more than 32 characters.",
        });
    }
    if (!regexp.test(username)) {
        errors.push({
            msg: "Invalid Username.",
        });
    }

    let user = await User.findOne({ username: username });
    if (user) {
        errors.push({ msg: "Username is already taken." });
    }

    if (errors.length) {
        return res.render("register", {
            errors,
            username,
            password,
            password2,
        });
    } else {
        const newUser = new User({
            username,
            password,
        });
        newUser
            .save()
            .then((user) => {
                req.flash("success", "You are now registered.");
                return res.redirect("/user/login");
            })
            .catch((err) => {
                req.flash("success", "DB error LOL.");
                console.log(err);
                return res.redirect("/user/login");
            });
    }
});

// login post
router.post("/login", (req, res) => {
    let { username, password } = req.body;
    User.findOne({ username: username }).then((user) => {
        if (!user) {
            return res.render("login", {
                errors: [{ msg: "Username does not exit." }],
                username,
                password,
            });
        } else if (user.password !== password) {
            return res.render("login", {
                errors: [{ msg: "Password or Username not correct." }],
                username,
                password,
            });
        } else {
            if (user.username === "admin") {
                console.log(new Date().toLocaleString() + ": admin logged in.");
            }
            req.session.username = user.username;
            res.cookie("login", "1", {
                maxAge: 900000,
                httpOnly: true,
            });
            return res.redirect("/");
        }
    });
});

module.exports = router;
