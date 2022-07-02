const { createUser, getInstanceByUserId, getUsers, updateUser, deleteUser, login, createNewInstance, updateInstance, deleteinstance, getArclightUsers, getArcUserConfig } = require("./user.controller")
const router = require("express").Router(); // create a router instance
const { checkToken } = require("../../auth/token_validation");

//auth routes
router.post("/", createUser);
router.get("/", getUsers);
router.patch("/", updateUser);
// router.delete("/:id", deleteUser);
router.post("/login", login);
router.get("/a", checkToken, getArclightUsers);
//arc routes
router.get("/arcuser/:id", getInstanceByUserId); //changed route path to not conflict with other routes
router.post("/createinstance", checkToken, createNewInstance);
router.patch("/updateinstance", updateInstance);
router.delete("/deleteinstance", deleteinstance);
router.get("/auc/:email", checkToken, getArcUserConfig);

module.exports = router;
