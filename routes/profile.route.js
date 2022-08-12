const {User} = require('../models/user.model')
const router = require('express').Router();
const mongoose = require('mongoose');

//user profiles
router.get('/getprofile/:id', async (req, res, next) => {
    try {
        const { id } = req.params;
        if (!mongoose.Types.ObjectId.isValid(id)) {
            req.flash('error', 'Invalid Id');
            res.redirect('/admin/users');
            return;
        }
        const person = await User.findById(id);
        res.render('profile', { person })
    } catch (error) {
        next(error)
    }
})

module.exports = router;