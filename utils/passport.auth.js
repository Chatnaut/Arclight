const passport = require('passport');
const LocalStrategy = require('passport-local').Strategy;
const {User} = require('../models/user.model');

const passportJWT = require("passport-jwt");
const JWTStrategy = passportJWT.Strategy;
const ExtractJWT = passportJWT.ExtractJwt;

passport.use(
    new LocalStrategy({
        usernameField: "email",
        passwordField: "password"
    }, async (email, password, done) => {
        try {
            const user = await User.findOne({ email: email });
            if (!user) {
                return done(null, false, { message: "Username/Email not registered" }) //error, usernameexists, message
            }
            //email exists let's verify the password and assign jwt token
            const isMatch = await user.isvalidPassword(password);
            if (isMatch) {
                return done(null, user)

            } else {
                return done(null, false, { message: "Incorrect Password" })
            }
        } catch (error) {
            done(error);
        }
    })
);

//Automatic session building cookie for persistent login by passport library
passport.serializeUser(function (user, done) {
    done(null, user.id);
});

passport.deserializeUser(function (id, done) {
    User.findById(id, function (err, user) {
        done(err, user);
    });
})


//JWT Authentication middleware using passport and continue to next middleware
passport.use(
    new JWTStrategy({
        jwtFromRequest: ExtractJWT.fromAuthHeaderAsBearerToken(),
        secretOrKey: process.env.AUTH_KEY
    }, async (jwt_payload, done) => {
        try {
            console.log(jwt_payload);
            const user = await User.findById(jwt_payload.user
                ._id);
            if (!user) {
                return done(null, false);
            }
            return done(null, user);
        } catch (error) {
            done(error);
        }
    }
    )
);

