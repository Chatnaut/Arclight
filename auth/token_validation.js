const { json } = require('express');
const { verify } = require('jsonwebtoken');
//access auth key from env
const authKey = process.env.AUTH_KEY;
module.exports = {
    checkToken: (req, res, next) => {
        let token = req.get("authorization");
        if (token) {
            token = token.slice(7); //remove "Bearer " from token and get only the token
            verify(token, authKey, (err, decoded) => {
                if(err){
                    res.json({
                        success:0,
                        messaage: "Invalid Token"
                    })
                }else{
                    next();
                }
            })
        } else {
            res.json({
                success: 0,
                message: "Access Denied! Unauthorized User"
            });
        }
    }
}