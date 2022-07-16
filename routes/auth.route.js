const router = require('express').Router();
const User = require('../models/user.model');
const { body, validationResult } = require('express-validator');
const jwt = require('jsonwebtoken');
const passport = require('passport');
const { ensureLoggedIn, ensureLoggedOut } = require('connect-ensure-login');

// router.get('/login', ensureLoggedOut({redirectTo: '/'}), async (req, res, next) => {
//     res.render("login");
// })

router.get('/register', ensureLoggedOut({ redirectTo: '/' }), async (req, res, next) => {
    // req.flash('error', "Some error")
    // req.flash('error', "Some error2")
    // req.flash('key', 'some value')
    // const messages = req.flash() //object
    // res.render("register", {messages});
    res.render("register");
})

router.post('/login', function (req, res, next) {
    passport.authenticate('local', { session: false }, (err, user, info) => {
        if (err || !user) {
            return res.status(400).json({
                message: info.message,
                user: user
            });
        }
        req.login(user, { session: false }, (err) => {
            if (err) {
                res.send(err);
                return;
            }
            // generate a token for the user
            const token = jwt.sign({ user }, process.env.AUTH_KEY, { expiresIn: '1h' });
            return res.status(200).json({ user, token });
        });
    })(req, res);
}
);



router.post('/register', ensureLoggedOut({ redirectTo: '/' }), [
    body('email').trim().isEmail().withMessage('Email must be a valid email').normalizeEmail().toLowerCase(),
    body('password').trim().isLength(4).withMessage('Password must be of 4 characters and above'),
    body('confirmpassword').custom((value, { req }) => {
        if (value !== req.body.password) {
            throw new Error('Password do not match')
        }
        return true //return success of this validator
    }) //validation & sanitization
], async (req, res, next) => {
    try {
        const errors = validationResult(req)
        if (!errors.isEmpty()) {
            errors.array().forEach(error => {
                req.flash('error', error.msg)
            })
            res.render('register', { email: req.body.email, messages: req.flash() })
            return
        }
        const { email } = req.body;
        const doesExists = await User.findOne({ email: email })
        if (doesExists) {
            res.redirect('auth/login')
            return;
        }
        const user = new User(req.body);
        await user.save()
        req.flash('success', `${user.email} registered successfully, you can now login.`)
        res.redirect('./auth/login')
        // res.send(user); //sending user object to frontend
    } catch (error) {
        next(error)
    }
})

router.post('/logout', ensureLoggedIn({ redirectTo: '/' }), async (req, res, next) => {
    req.logout();
    req.session.destroy();
    res.redirect('/auth/login');
})

module.exports = router;

// //my custom function to avoid user to unauthorised sessions routes [replaced with connectEnsure plugin]
// function ensureAuthenticated(req, res, next) {
//     if (req.isAuthenticated()) {
//         next();
//     } else {
//         res.redirect('/auth/login');
//     }
// };
// function ensureNotAuthenticated(req, res, next) { //custom function to avoid user to unauthorised sessions routes
//     if (req.isAuthenticated()) {
//         res.redirect('back');
//     } else {
//         next();
//     }
// };