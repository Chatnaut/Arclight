"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const ava_1 = __importDefault(require("ava"));
const supertest_1 = __importDefault(require("supertest"));
const express_1 = __importDefault(require("express"));
const express_session_1 = __importDefault(require("express-session"));
const __1 = __importDefault(require("../"));
function createSupertetAgent(sessionOpts, mongoStoreOpts) {
    const app = express_1.default();
    const store = __1.default.create(mongoStoreOpts);
    app.use(express_session_1.default({
        ...sessionOpts,
        store: store,
    }));
    app.get('/', function (req, res) {
        if (typeof req.session.views === 'number') {
            req.session.views++;
        }
        else {
            req.session.views = 0;
        }
        res.status(200).send({ views: req.session.views });
    });
    app.get('/ping', function (req, res) {
        res.status(200).send({ views: req.session.views });
    });
    const agent = supertest_1.default.agent(app);
    return agent;
}
function createSupertetAgentWithDefault(sessionOpts = {}, mongoStoreOpts = {}) {
    return createSupertetAgent({ secret: 'foo', ...sessionOpts }, {
        mongoUrl: 'mongodb://root:example@127.0.0.1:27017',
        dbName: 'itegration-test-db',
        stringify: false,
        ...mongoStoreOpts,
    });
}
ava_1.default.serial.cb('simple case', (t) => {
    const agent = createSupertetAgentWithDefault();
    agent
        .get('/')
        .expect(200)
        .then((response) => response.headers['set-cookie'])
        .then((cookie) => {
        agent
            .get('/')
            .expect(200)
            .end((err, res) => {
            t.is(err, null);
            t.deepEqual(res.body, { views: 1 });
            return t.end();
        });
    });
});
ava_1.default.serial.cb('simple case with touch after', (t) => {
    const agent = createSupertetAgentWithDefault({ resave: false, saveUninitialized: false, rolling: true }, { touchAfter: 1 });
    agent
        .get('/')
        .expect(200)
        .then(() => {
        agent
            .get('/')
            .expect(200)
            .end((err, res) => {
            t.is(err, null);
            t.deepEqual(res.body, { views: 1 });
            new Promise((resolve) => {
                setTimeout(() => {
                    resolve();
                }, 1200);
            }).then(() => {
                agent
                    .get('/ping')
                    .expect(200)
                    .end((err, res) => {
                    t.is(err, null);
                    t.deepEqual(res.body, { views: 1 });
                    return t.end();
                });
            });
        });
    });
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW50ZWdyYXRpb24uc3BlYy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uL3NyYy90ZXN0L2ludGVncmF0aW9uLnNwZWMudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQSw4Q0FBc0I7QUFDdEIsMERBQStCO0FBQy9CLHNEQUE2QjtBQUM3QixzRUFBeUQ7QUFDekQsNENBQTRCO0FBUzVCLFNBQVMsbUJBQW1CLENBQzFCLFdBQTJCLEVBQzNCLGNBQW1DO0lBRW5DLE1BQU0sR0FBRyxHQUFHLGlCQUFPLEVBQUUsQ0FBQTtJQUNyQixNQUFNLEtBQUssR0FBRyxXQUFVLENBQUMsTUFBTSxDQUFDLGNBQWMsQ0FBQyxDQUFBO0lBQy9DLEdBQUcsQ0FBQyxHQUFHLENBQ0wseUJBQU8sQ0FBQztRQUNOLEdBQUcsV0FBVztRQUNkLEtBQUssRUFBRSxLQUFLO0tBQ2IsQ0FBQyxDQUNILENBQUE7SUFDRCxHQUFHLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxVQUFVLEdBQUcsRUFBRSxHQUFHO1FBQzdCLElBQUksT0FBTyxHQUFHLENBQUMsT0FBTyxDQUFDLEtBQUssS0FBSyxRQUFRLEVBQUU7WUFDekMsR0FBRyxDQUFDLE9BQU8sQ0FBQyxLQUFLLEVBQUUsQ0FBQTtTQUNwQjthQUFNO1lBQ0wsR0FBRyxDQUFDLE9BQU8sQ0FBQyxLQUFLLEdBQUcsQ0FBQyxDQUFBO1NBQ3RCO1FBQ0QsR0FBRyxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUMsRUFBRSxLQUFLLEVBQUUsR0FBRyxDQUFDLE9BQU8sQ0FBQyxLQUFLLEVBQUUsQ0FBQyxDQUFBO0lBQ3BELENBQUMsQ0FBQyxDQUFBO0lBQ0YsR0FBRyxDQUFDLEdBQUcsQ0FBQyxPQUFPLEVBQUUsVUFBVSxHQUFHLEVBQUUsR0FBRztRQUNqQyxHQUFHLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQyxFQUFFLEtBQUssRUFBRSxHQUFHLENBQUMs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