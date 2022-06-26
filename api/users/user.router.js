const { createUser, getInstanceByUserId, getUsers, updateUser, deleteUser, login, createNewInstance, getArclightUsers, getArcUserConfig } = require("./user.controller")
const router = require("express").Router(); // create a router instance
const { checkToken } = require("../../auth/token_validation");

router.post("/", createUser);
router.get("/", getUsers);
router.get("/arcuser/:id", getInstanceByUserId); //changed route path to not conflict with other routes
router.patch("/", checkToken, updateUser);
router.delete("/", checkToken, deleteUser);
router.post("/login", login);
router.post("/createinstance", checkToken, createNewInstance);
router.get("/a", checkToken, getArclightUsers);
router.get("/auc/:email", checkToken, getArcUserConfig);

module.exports = router;
