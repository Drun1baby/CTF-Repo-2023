const User = require("../models/User");

module.exports = {
    initdb: () => {
        // add Admin
        User.findOneAndUpdate(
            {
                username: "admin",
            },
            {
                $setOnInsert: {
                    password: process.env.ADMIN_PASS || "admin",
                },
            },
            {
                upsert: true,
                new: true,
            }
        )
            .then(console.log("[+] Admin added"))
            .catch((err) => console.log(err));
    },
};
