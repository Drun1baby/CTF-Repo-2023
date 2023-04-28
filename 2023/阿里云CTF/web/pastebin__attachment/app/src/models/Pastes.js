const mongoose = require("mongoose");

const Pastesschema = new mongoose.Schema({
    pasteid: {
        type: String,
        required: true,
    },
    username: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
        maxlength: 64,
    },
    content: {
        type: String,
        required: true,
    },
    date: { type: Date, default: Date.now },
    score: {
        type: Number,
        required: false,
        default: -1,
        validate: {
            validator: Number.isInteger,
            message: "{VALUE} is not an integer value",
        },
    },
});

const Pastes = mongoose.model("Pastes", Pastesschema);
module.exports = Pastes;
