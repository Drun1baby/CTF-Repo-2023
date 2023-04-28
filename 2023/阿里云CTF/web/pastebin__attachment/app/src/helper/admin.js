const ensureAdmin = (req, res, next) => {
    // return next();
    if (req.session.username === "admin") {
        return next();
    } else {
        console.log(new Date() + ": Not admin");
        return res.status(403).send("You are not admin");
    }
};

module.exports = {
    ensureAdmin,
};
