const router = require('express').Router();
const {User} = require('../models/user.model');
const { body, validationResult } = require('express-validator');
const jwt = require('jsonwebtoken');
const passport = require('passport');
const { ensureLoggedIn, ensureLoggedOut } = require('connect-ensure-login');

router.post('/login', function (req, res, next) {
    passport.authenticate('local', { session: false,
     }, (err, user, info) => {
        if (err || !user) {
            req.flash('error', info.message)
            return res.status(400).json({
                success: 0,
                message: req.flash(),
                user: user
            });

        }
        req.login(user, { session: false }, (err) => {
            if (err) {
                req.flash('error', info.message)
                return res.status(500).json({
                    success: 0,
                    message: req.flash(),
                    user: user
                });
            }
            // generate a token for the user
            const token = jwt.sign({ user }, process.env.AUTH_KEY, { expiresIn: '5h' });
            req.flash('success', "User logged in successfully")
            return res.status(200).json({ 
                success: 1,
                message: req.flash(),
                user: user,
                token: token
            });
        }
        );
    }
    )(req, res, next);
}
);

router.post('/register', ensureLoggedOut({ redirectTo: '/' }), [
    body('username').not().isEmpty().withMessage('Name is required'),
    body('email').trim().isEmail().withMessage('Email must be a valid email').normalizeEmail().toLowerCase(),
    body('password').trim().isLength(4).withMessage('Password must be of 4 characters and above'),
    // body('confirmpassword').custom((value, { req }) => {
    //     if (value !== req.body.password) {
    //         throw new Error('Password do not match')
    //     }
    //     return true //return success of this validator
    // }) //validation & sanitization
], async (req, res, next) => {
    try {
        const errors = validationResult(req)
        if (!errors.isEmpty()) {
            errors.array().forEach(error => {
                req.flash('error', error.msg)
            })
            return res.status(200).json({
                success: 0,
                message: req.flash(),
            });

        }
        const { email } = req.body;
        const doesExists = await User.findOne({ email: email })
        if (doesExists) {
            req.flash('error', "Email already exists")
            return res.status(200).json({
                success: 0,
                message: req.flash(),
            });
        }
        const user = new User(req.body);
        await user.save()
        req.flash('success', `${user.email} registered successfully, you can now sign in`)
        return res.status(200).json({
            success: 1,
            message: req.flash(),
        });
        
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