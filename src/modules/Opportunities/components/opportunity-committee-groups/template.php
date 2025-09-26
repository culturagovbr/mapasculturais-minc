<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\EvaluationMethod;
use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    mc-icon
    mc-popover
    mc-tab
    mc-tabs
    mc-toggle
    mc-alert
    opportunity-evaluation-committee
    opportunity-registration-filter-configuration
');

$next_execution_time = EvaluationMethod::getNextRedistributionDateTime();
?>

<div class="opportunity-committee-groups">
    <div class="opportunity-committee-groups__description">
       <p><?php i::_e('Defina os agentes que farão parte das comissões de avaliação desta fase.') ?></p>
    </div>

    <mc-tabs ref="tabs">
        <template #after-tablist>
            <div class="opportunity-committee-groups__actions">
                <button v-if="hasTwoOrMoreGroups && entity.useCommitteeGroups" class="button button--icon button--primary button--sm" @click="addGroup(minervaGroup, true);">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Voto de minerva') ?>
                </button>
                
                <mc-popover openside="down-right">
                    <template #button="popover">
                        <button @click="popover.toggle()" class="button button--primary-outline button--sm button--icon">
                            <mc-icon name="add"></mc-icon>
                            <?php i::_e("Adicionar comissão") ?>
                        </button>
                    </template>
                    
                    <template #default="popover">
                        <form @submit="groupCreation($event, popover)">
                            <div class="grid-12">
                                <div class="related-input col-12">
                                    <input v-model="newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome da comissão') ?>" maxlength="64" />
                                </div>
                                <button class="col-6 button button--text" type="reset" @click="cancelGroupCreation(popover)"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </mc-popover>
            </div>
        </template>
        
        <mc-tab v-for="([groupName, relations], index) in Object.entries(entity.relatedAgents)" :key="index" :label="groupName == '@tiebreaker' ? '<?= $this->text('tiebreaker', i::__('Voto de minerva')) ?>' : groupName" :slug="String(index)">
            <div class="opportunity-committee-groups__group">
                <div class="opportunity-committee-groups__edit-group field">
                    <!-- <label v-if="groupName != '@tiebreaker'" :for="`newGroupName${index}`"><?= i::__('Título da comissão') ?></label> -->

                    <div class="opportunity-committee-groups__edit-group--field">
                        <!-- <input v-if="groupName != '@tiebreaker'" id="newGroupName${index}`" class="input" type="text" @change="renameTab($event, index, groupName);" placeholder="<?=  i::esc_attr__('Digite o novo nome da comissão') ?>" /> -->
                        <mc-confirm-button @confirm="removeGroup(groupName)">
                            <template #button="modal">
                                <a class="button button--delete button--icon button--sm" @click="modal.open()">
                                    <mc-icon name="trash"></mc-icon>
                                    <?= i::__('Excluir comissão') ?> 
                                </a>
                            </template>
                            <template #message="message">
                                <?php i::_e('Remover comissão de avaliadores?') ?>
                            </template>
                        </mc-confirm-button>
                    </div> 
                </div>

                <div class="opportunity-committee-groups__multiple-evaluators">
                    <div class="field">
                        <mc-toggle
                            :modelValue="entity.valuersPerRegistration[groupName] !== undefined" 
                            @update:modelValue="enableValuersPerRegistration($event, groupName)"
                            label="<?= i::__('Limitar número de avaliadores por inscrição') ?>"
                        />
                        <input v-if="entity.valuersPerRegistration[groupName] !== undefined" 
                            v-model="entity.valuersPerRegistration[groupName]" type="number" @change="autoSave()"/>

                        <div v-if="entity.valuersPerRegistration[groupName] !== undefined" class="has-info">
                            <mc-toggle 
                                :modelValue="entity.ignoreStartedEvaluations[groupName] !== undefined" 
                                @update:modelValue="enableIgnoreStartedEvaluations($event, groupName)"
                                label="<?= i::__('Desconsiderar as avaliações já feitas na distribuição') ?>"
                            />

                            <?php $this->info('editais-oportunidades -> configuracoes -> desconsiderar-avaliacoes-na-distribuicao') ?>
                        </div>
                        
                    </div>
    
                    <div class="field">
                        <mc-toggle
                            :modelValue="entity.fetchFields[groupName] !== undefined" 
                            @update:modelValue="enableRegisterFilterConf($event, groupName)"
                            label="<?= i::__('Configuração filtro de inscrição para avaliadores/comissão') ?>"
                        />
                        <opportunity-registration-filter-configuration 
                            v-if="entity.fetchFields[groupName] !== undefined" 
                            :entity="entity"
                            v-model:default-value="entity.fetchFields[groupName]"
                            :excludeFields="globalExcludeFields"
                            @updateExcludeFields="updateExcludedFields('global', $event)"
                            useDistributionField
                            is-global
                        >
                        </opportunity-registration-filter-configuration>
                    </div>

                    <div class="field">
                        <mc-toggle 
                            v-if="groupName == minervaGroup"
                            :modelValue="entity?.showExternalReviews"
                            @update:modelValue="enableExternalReviews"
                            label="<?= i::__('Permitir visualização de pareceres externos') ?>"
                        />
                    </div>
                </div>
                
                <div class="opportunity-committee-groups__evaluators">
                    <mc-tabs>
                        <mc-tab label="<?php i::esc_attr_e("Habilitados")?>" slug="habilitados">
                            <opportunity-evaluation-committee :entity="entity" :group="groupName" :excludeFields="individualExcludeFields" @updateExcludeFields="updateExcludedFields('individual', $event)"></opportunity-evaluation-committee>
                        </mc-tab>

                        <mc-tab label="<?php i::esc_attr_e("Desabilitados")?>" slug="desabilitados">
                            <opportunity-evaluation-committee :entity="entity" :group="groupName" :excludeFields="individualExcludeFields" show-disabled @updateExcludeFields="updateExcludedFields('individual', $event)"></opportunity-evaluation-committee>
                        </mc-tab>
                    </mc-tabs>
                </div>
            </div>
        </mc-tab>
    </mc-tabs>

    <div class="opportunity-committee-groups__distribution">
        <h3 class="opportunity-committee-groups__distribution-title">
            <?= i::__('Distribuição das avaliações') ?>
            <?php $this->info('editais-oportunidades -> configuracoes -> agendamento-distribuicao') ?>
        </h3>

        <div v-if="statusMessage" class="opportunity-committee-groups__distribution-progress">
            <span class="opportunity-committee-groups__distribution-progressbar" :style="{width: percentage}"></span>
            <span class="opportunity-committee-groups__distribution-message"> {{statusMessage}} </span>
        </div>

        <template v-else>
            <div class="opportunity-committee-groups__distribution-content">
                <mc-alert type="warning" >
                    <?= sprintf(i::__('As inscrições serão distribuidas para as comissões às %s'), $next_execution_time->format('H:i')); ?>
                </mc-alert>
            </div>

            <div class="opportunity-committee-groups__distribution-actions">
                <button @click="distriuteEvaluations()" class="button button--primary">
                    <?= i::__('Distribuir avaliações agora') ?>
                </button>

                <mc-loading :condition="distributingEvaluations" class="opportunity-committee-groups__loading">
                    <?= i::__('Agendando distribuição de avaliações.') ?>
                </mc-loading>
            </div>
        </template>
    </div>
</div>
