import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(
    new URL('../../src/modules/Opportunities/components/opportunity-phases-config/script.js', import.meta.url),
    'utf8'
);

let component;
vm.runInNewContext(source, {
    app: {
        component(name, definition) {
            if (name === 'opportunity-phases-config') {
                component = definition;
            }
        },
    },
    $TEMPLATES: { 'opportunity-phases-config': '' },
    Entity: class Entity {},
    Utils: { getTexts: () => () => '' },
});

test('shows the publication date of a consecutive evaluation phase', () => {
    const firstPhase = { __objectType: 'opportunity' };
    const firstEvaluation = {
        __objectType: 'evaluationmethodconfiguration',
        opportunity: { publishTimestamp: null },
    };
    const secondEvaluation = {
        __objectType: 'evaluationmethodconfiguration',
        opportunity: { publishTimestamp: { date: '2030-01-15 12:30:00' } },
    };
    const lastPhase = { __objectType: 'opportunity', isLastPhase: true };
    const context = {
        phases: [firstPhase, firstEvaluation, secondEvaluation, lastPhase],
    };
    context.getPreviousPhase = (phase) => component.methods.getPreviousPhase.call(context, phase);
    context.getNextPhase = (phase) => component.methods.getNextPhase.call(context, phase);

    assert.equal(
        component.methods.showPublishTimestamp.call(context, secondEvaluation),
        true
    );
});
