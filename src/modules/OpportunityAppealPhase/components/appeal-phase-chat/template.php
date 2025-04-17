<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-chat
    mc-select
');

?>

<mc-chat v-if="thread" :thread="thread" :ping-pong="true">
    <template v-if="userRequest === 'evaluation'" #message-payload="{ message, lastMessageIsMine }">
        <div class="mc-chat__reviewer-message field" :let="initMessage(message)">
            <label for="status"><?= i::__('Resultado da validação:') ?></label>
            <mc-select :default-value="message.payload.status" @change-option="message.payload.status = $event.value" id="status">
                <div v-for="(label, value) in statusList" :key="value" :value="value">
                    <mc-icon name="circle" :class="verifyState(value)"></mc-icon>
                    {{ label }}
                </div>
            </mc-select>
            
            <label for="agent-response"><?= i::__('Justificativa:') ?></label>
            <textarea
                v-model="message.payload.message" 
                ref="textarea" 
                placeholder="<?= i::__('Digite sua mensagem') ?>" 
                id="agent-response" 
                class="mc-chat__textarea">
            </textarea>

            <div class="mc-chat__endChat field">
                <input type="checkbox" v-model="message.payload.endChat" id="endChat"></input>
                <label for="endChat"><?= i::__('Encerrar processo') ?></label>
            </div>
    
            <textarea 
                v-if="message.payload.endChat"
                v-model="message.payload.justification" 
                ref="textarea" 
                placeholder="<?= i::__('Digite sua justificativa para encerrar o processo') ?>" 
                id="endChat-response" 
                class="mc-chat__textarea">
            </textarea>
        </div>
    </template>
    <template v-if="userRequest === 'view'" #message-payload="{ message, lastMessageIsMine }">
        <div class="field">
            <label for="agent-response"><?= i::__('Resposta do agente:') ?></label>
            <textarea 
                v-model="message.payload" 
                ref="textarea" 
                placeholder="<?= i::__('Digite sua mensagem') ?>" 
                id="agent-response" 
                class="mc-chat__textarea">
            </textarea>
        </div>
    </template>
    <template #default="{ message }">
        <div v-if="typeof message.payload === 'object'">
            <div class="mc-chat__evaluation">
                <div class="mc-chat__evaluation-status" v-if="message.payload.status != null">
                    <h4 class="semibold"><?= i::__('Resultado da validação:') ?></h4>
                    <div class="mc-chat__evaluation-status-content">
                        <mc-icon name="circle" :class="verifyState(message.payload.status)"></mc-icon>
                        <p v-if="message.payload.status == 10"><?= i::__('Deferido') ?></p>
                        <p v-else-if="message.payload.status == 3"><?= i::__('Indeferido') ?></p>
                        <p v-else-if="message.payload.status == 2"><?= i::__('Negado') ?></p>
                        <p v-else-if="message.payload.status == 8"><?= i::__('Suplente') ?></p>
                    </div>
                </div>
                <div class="mc-chat__evaluation-text">
                    <h4 class="semibold"><?= i::__('Justificativa ou observações:') ?></h4>
                    <p>{{message.payload.message}}</p>

                    <div v-if="message.files?.chatAttachment" class="mc-chat__attachment">
                        <entity-file
                            :entity="message"
                            group-name="chatAttachment"
                            classes="col-12"
                            ></entity-file>
                    </div>
                </div>
                <div class="mc-chat__evaluation-closed" v-if="message.payload.endChat">
                    <h4 class="semibold"><?= i::__('Justificativa para encerramento do processo:') ?></h4>
                    <p>{{message.payload.justification}}</p>
                </div>
            </div>
        </div>
    </template>
</mc-chat>