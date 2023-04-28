const express = require("express");
const router = express.Router();
const ensureAdmin = require("../helper/admin").ensureAdmin;
const Pastes = require("../models/Pastes");
const redis = require("redis");
const { randomBytes } = require("crypto");
let auth_code = "";
let fail = 0;

router.get("/paste/:pasteid/view", ensureAdmin, async (req, res) => {
    let pasteid = req.params.pasteid;
    if (auth_code === undefined || auth_code === "") {
        auth_code = randomBytes(100).toString("hex").slice(0, 5);
    }

    let regex =
        /^[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i;
    let match = regex.exec(pasteid);
    if (!match) {
        return res.send(403, { error: "Your paste id is wrong" });
    }

    let item = await Pastes.findOne({ pasteid: pasteid });

    if (!item) {
        return res.send({ msg: "Could not find such a paste id" });
    } else {
        return res.render("admin/paste", {
            title: item.title,
            content: item.content,
            auth_token: auth_code,
        });
    }
});

router.get("/purge", ensureAdmin, async (req, res) => {
    const client = redis.createClient({ url: "redis://localhost:6379" });
    await client.connect();
    client.on("error", (err) => console.log("Redis Client Error", err));
    await client.sendCommand(["flushall"]);
    await client.disconnect();
    return res.send({ msg: "clear" });
});

router.post("/paste/:pasteid/view", ensureAdmin, async (req, res) => {
    let { score, _auth } = req.body;
    let pasteid = req.params.pasteid;

    let regex =
        /^[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i;
    let match = regex.exec(pasteid);
    if (!match) {
        return res.send(403, { error: "Your paste id is wrong" });
    }

    if (auth_code === "") {
        auth_code = randomBytes(100).toString("hex").slice(0, 5);
    }
    if (_auth !== auth_code) {
        fail++;
        // no brute force plz
        if (fail == 20) {
            auth_code = randomBytes(100)
                .toString("hex")
                .slice(0, 5);
            fail = 0;
        }
        return res.send(403, { error: "Auth Token mismatch" });
    } else {
        auth_code = randomBytes(100).toString("hex").slice(0, 5);
        Pastes.findOneAndUpdate(
            { pasteid: pasteid },
            { score: score },
            (err, doc) => {
                if (err) {
                    return res.send(500, { error: "DB error" });
                } else {
                    return res.send("Rate successfully");
                }
            }
        );
    }
});

module.exports = router;
