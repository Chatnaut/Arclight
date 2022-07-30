const router = require('express').Router();

router.get('/getprofile', async(req,res,next) => {
    console.log(req.user)
    const person = req.user
    // res.render('profile', {person});
    res.status(200).json({
        success: 1,
        message: "Profile retrieved successfully",
        result: person
    });

})

module.exports = router;