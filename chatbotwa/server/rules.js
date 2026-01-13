const fs = require('fs');
const path = require('path');

const RULES_FILE = path.join(__dirname, 'rules.json');

// Ensure file exists
if (!fs.existsSync(RULES_FILE)) {
    fs.writeFileSync(RULES_FILE, JSON.stringify([], null, 2));
}

const getRules = () => {
    try {
        const data = fs.readFileSync(RULES_FILE, 'utf8');
        return JSON.parse(data);
    } catch (err) {
        console.error('Error reading rules:', err);
        return [];
    }
};

const saveRules = (rules) => {
    try {
        fs.writeFileSync(RULES_FILE, JSON.stringify(rules, null, 2));
        return true;
    } catch (err) {
        console.error('Error saving rules:', err);
        return false;
    }
};

const addRule = (rule) => {
    const rules = getRules();
    // Simple ID generation
    rule.id = Date.now().toString();
    rules.push(rule);
    saveRules(rules);
    return rule;
};

const deleteRule = (id) => {
    let rules = getRules();
    const initialLength = rules.length;
    rules = rules.filter(r => r.id !== id);
    if (rules.length !== initialLength) {
        saveRules(rules);
        return true;
    }
    return false;
};

module.exports = { getRules, addRule, deleteRule };
