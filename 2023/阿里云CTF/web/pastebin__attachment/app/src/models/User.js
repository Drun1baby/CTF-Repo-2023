const mongoose = require("mongoose");

const UserSchema = new mongoose.Schema({
    username: {
        type: String,
        required: true,
        maxlength: 32,
    },
    password: {
        type: String,
        required: true,
        maxlength: 32,
    },
    date: {
        type: String,
        default: Date.now(),
    },
});

const User = mongoose.model("User", UserSchema);
module.exports = User;
