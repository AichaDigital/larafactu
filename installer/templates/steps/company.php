<?php
/**
 * Company step template
 *
 * @var array $countries
 * @var array $currencies
 * @var array $legalEntityTypes
 */
?>
<div id="step-company" x-data="{ isRoi: false }">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('company.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('company.description') ?></p>

    <form id="step-form" class="space-y-6">

        <!-- Business Info -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <h3 class="text-white font-medium mb-4"><?= __('company.business_info') ?></h3>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-slate-300 mb-1">
                            <?= __('company.business_name') ?> <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="business_name"
                            id="business_name"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="Mi Empresa S.L."
                            required
                        >
                    </div>

                    <div>
                        <label for="legal_entity_type" class="block text-sm font-medium text-slate-300 mb-1">
                            <?= __('company.legal_entity_type') ?? 'Tipo de entidad' ?> <span class="text-red-400">*</span>
                        </label>
                        <select
                            name="legal_entity_type"
                            id="legal_entity_type"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            required
                        >
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($legalEntityTypes as $code => $name) { ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $code === 'LIMITED_COMPANY' ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="tax_id" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('company.tax_id') ?> <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="tax_id"
                        id="tax_id"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent uppercase"
                        placeholder="B12345678"
                        required
                    >
                    <p class="text-xs text-slate-500 mt-1"><?= __('company.tax_id_help') ?></p>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <h3 class="text-white font-medium mb-4"><?= __('company.address') ?></h3>

            <div class="space-y-4">
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('company.address_line1') ?> <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="address"
                        id="address"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="Calle Principal 123, Piso 2"
                        required
                    >
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label for="zip_code" class="block text-sm font-medium text-slate-300 mb-1">
                            <?= __('company.postal_code') ?> <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="zip_code"
                            id="zip_code"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="28001"
                            required
                        >
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-slate-300 mb-1">
                            <?= __('company.city') ?> <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="city"
                            id="city"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="Madrid"
                            required
                        >
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-slate-300 mb-1">
                            <?= __('company.province') ?>
                        </label>
                        <input
                            type="text"
                            name="state"
                            id="state"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="Madrid"
                        >
                    </div>

                    <div>
                        <label for="country_code" class="block text-sm font-medium text-slate-300 mb-1">
                            <?= __('company.country') ?> <span class="text-red-400">*</span>
                        </label>
                        <select
                            name="country_code"
                            id="country_code"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            required
                        >
                            <?php foreach ($countries as $code => $name) { ?>
                            <option value="<?= $code ?>" <?= $code === 'ES' ? 'selected' : '' ?>><?= $name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- EU/ROI Options -->
        <div class="bg-slate-700/50 rounded-lg p-4">
            <h3 class="text-white font-medium mb-4"><?= __('company.eu_options') ?></h3>

            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_roi"
                        id="is_roi"
                        value="1"
                        class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                        x-model="isRoi"
                    >
                    <span class="text-slate-300"><?= __('company.is_roi') ?></span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_oss"
                        id="is_oss"
                        value="1"
                        class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                    >
                    <span class="text-slate-300"><?= __('company.is_oss') ?? 'Operador OSS (One Stop Shop)' ?></span>
                </label>

                <div>
                    <label for="currency" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('company.currency') ?>
                    </label>
                    <select
                        name="currency"
                        id="currency"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    >
                        <?php foreach ($currencies as $code => $name) { ?>
                        <option value="<?= $code ?>" <?= $code === 'EUR' ? 'selected' : '' ?>><?= $name ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

    </form>
</div>


