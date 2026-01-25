<?php
/**
 * Database step template
 *
 * @var array $defaults
 * @var bool $docker_available
 * @var array|null $docker_config
 * @var array $db_options
 * @var string $default_db_type
 */
$dockerConfig = $docker_config ?? null;
$dockerAvailable = $docker_available ?? false;
$defaultDbType = $default_db_type ?? 'mysql';
?>
<div id="step-database" x-data="{
    dbType: '<?= htmlspecialchars($defaultDbType) ?>',
    showMysqlPassword: false
}">
    <h2 class="text-2xl font-bold text-white mb-2"><?= __('database.title') ?></h2>
    <p class="text-slate-400 mb-6"><?= __('database.description') ?></p>

    <form id="step-form" class="space-y-6">

        <!-- Database Type Selector -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-300 mb-3">
                <?= __('database.select_type') ?? 'Database Type / Tipo de Base de Datos' ?> <span class="text-red-400">*</span>
            </label>

            <div class="space-y-3">
                <?php if ($dockerAvailable) { ?>
                <!-- Docker MySQL Option -->
                <label
                    class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors"
                    :class="dbType === 'docker' ? 'bg-brand-900/30 border-brand-500' : 'bg-slate-700/50 border-slate-600 hover:bg-slate-700'"
                    @click="dbType = 'docker'"
                >
                    <input
                        type="radio"
                        name="db_type"
                        value="docker"
                        x-model="dbType"
                        class="sr-only"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">MySQL (Docker)</span>
                            <span class="px-2 py-0.5 text-xs bg-green-600 text-white rounded"><?= __('database.recommended') ?? 'Recommended' ?></span>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.docker_description') ?? 'Predefined Docker MySQL. No configuration needed.' ?>
                        </p>
                        <?php if ($dockerConfig) { ?>
                        <p class="text-xs text-slate-500 mt-1">
                            Host: <?= htmlspecialchars($dockerConfig['host']) ?>:<?= htmlspecialchars((string) $dockerConfig['port']) ?> |
                            DB: <?= htmlspecialchars($dockerConfig['database']) ?>
                        </p>
                        <?php } ?>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                            :class="dbType === 'docker' ? 'border-brand-500 bg-brand-500' : 'border-slate-500'"
                        >
                            <svg x-show="dbType === 'docker'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </label>
                <?php } ?>

                <!-- Custom MySQL Option -->
                <label
                    class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors"
                    :class="dbType === 'mysql' ? 'bg-brand-900/30 border-brand-500' : 'bg-slate-700/50 border-slate-600 hover:bg-slate-700'"
                    @click="dbType = 'mysql'"
                >
                    <input
                        type="radio"
                        name="db_type"
                        value="mysql"
                        x-model="dbType"
                        class="sr-only"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">MySQL (Custom)</span>
                            <?php if (! $dockerAvailable) { ?>
                            <span class="px-2 py-0.5 text-xs bg-brand-600 text-white rounded"><?= __('database.recommended') ?? 'Recommended' ?></span>
                            <?php } ?>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.mysql_description') ?? 'Configure your own MySQL server credentials.' ?>
                        </p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                            :class="dbType === 'mysql' ? 'border-brand-500 bg-brand-500' : 'border-slate-500'"
                        >
                            <svg x-show="dbType === 'mysql'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </label>

                <!-- SQLite Option -->
                <label
                    class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors"
                    :class="dbType === 'sqlite' ? 'bg-brand-900/30 border-brand-500' : 'bg-slate-700/50 border-slate-600 hover:bg-slate-700'"
                    @click="dbType = 'sqlite'"
                >
                    <input
                        type="radio"
                        name="db_type"
                        value="sqlite"
                        x-model="dbType"
                        class="sr-only"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">SQLite</span>
                            <span class="px-2 py-0.5 text-xs bg-slate-600 text-slate-300 rounded"><?= __('database.simple') ?? 'Simple' ?></span>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.sqlite_description') ?? 'File-based database. Good for testing and small deployments.' ?>
                        </p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <div
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                            :class="dbType === 'sqlite' ? 'border-brand-500 bg-brand-500' : 'border-slate-500'"
                        >
                            <svg x-show="dbType === 'sqlite'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Docker MySQL Info (read-only display) -->
        <div x-show="dbType === 'docker'" x-cloak class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-blue-200 text-sm">
                    <strong><?= __('database.docker_info_title') ?? 'Docker MySQL Configuration' ?></strong>
                    <p class="mt-1 text-blue-300/80">
                        <?= __('database.docker_info_desc') ?? 'The following predefined values will be used automatically:' ?>
                    </p>
                    <?php if ($dockerConfig) { ?>
                    <ul class="mt-2 space-y-1 text-blue-300/70 text-xs font-mono">
                        <li>Host: <?= htmlspecialchars($dockerConfig['host']) ?></li>
                        <li>Port: <?= htmlspecialchars((string) $dockerConfig['port']) ?></li>
                        <li>Database: <?= htmlspecialchars($dockerConfig['database']) ?></li>
                        <li>Username: <?= htmlspecialchars($dockerConfig['username']) ?></li>
                    </ul>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Custom MySQL Form -->
        <div x-show="dbType === 'mysql'" x-cloak class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Host -->
                <div>
                    <label for="host" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('database.host') ?> <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="host"
                        id="host"
                        value="<?= htmlspecialchars($defaults['host'] ?? '127.0.0.1') ?>"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        :required="dbType === 'mysql'"
                    >
                </div>

                <!-- Port -->
                <div>
                    <label for="port" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('database.port') ?> <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="number"
                        name="port"
                        id="port"
                        value="<?= htmlspecialchars($defaults['port'] ?? '3306') ?>"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        :required="dbType === 'mysql'"
                    >
                </div>
            </div>

            <!-- Database Name -->
            <div>
                <label for="database" class="block text-sm font-medium text-slate-300 mb-1">
                    <?= __('database.name') ?> <span class="text-red-400">*</span>
                </label>
                <input
                    type="text"
                    name="database"
                    id="database"
                    value="<?= htmlspecialchars($defaults['database'] ?? 'larafactu') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    :required="dbType === 'mysql'"
                >
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('database.username') ?> <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="<?= htmlspecialchars($defaults['username'] ?? 'root') ?>"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        :required="dbType === 'mysql'"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">
                        <?= __('database.password') ?>
                    </label>
                    <div class="relative">
                        <input
                            :type="showMysqlPassword ? 'text' : 'password'"
                            name="password"
                            id="password"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 pr-10 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        >
                        <button
                            type="button"
                            @click="showMysqlPassword = !showMysqlPassword"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-white"
                        >
                            <svg x-show="!showMysqlPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showMysqlPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Create if not exists -->
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    name="create_if_not_exists"
                    value="1"
                    checked
                    class="w-5 h-5 rounded bg-slate-600 border-slate-500 text-brand-600 focus:ring-brand-500"
                >
                <span class="text-slate-300"><?= __('database.create_if_not_exists') ?></span>
            </label>

            <div class="bg-blue-900/30 border border-blue-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-blue-200 text-sm">
                        <strong><?= __('database.mysql_note_title') ?? 'Note' ?>:</strong>
                        <?= __('database.mysql_note') ?? 'Ensure the MySQL user has permissions to create databases if the database does not exist yet.' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQLite Form -->
        <div x-show="dbType === 'sqlite'" x-cloak class="space-y-4">
            <div>
                <label for="sqlite_path" class="block text-sm font-medium text-slate-300 mb-1">
                    <?= __('database.sqlite_path') ?? 'Database Path' ?> <span class="text-red-400">*</span>
                </label>
                <input
                    type="text"
                    name="sqlite_path"
                    id="sqlite_path"
                    value="<?= htmlspecialchars($defaults['sqlite_path'] ?? 'database/database.sqlite') ?>"
                    class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent font-mono text-sm"
                    :required="dbType === 'sqlite'"
                >
                <p class="text-xs text-slate-500 mt-1">
                    <?= __('database.sqlite_path_hint') ?? 'Relative to application root. The file will be created if it does not exist.' ?>
                </p>
            </div>

            <div class="bg-amber-900/30 border border-amber-500/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-amber-200 text-sm">
                        <strong><?= __('database.sqlite_warning_title') ?? 'Important' ?>:</strong>
                        <?= __('database.sqlite_warning') ?? 'SQLite is suitable for development and small deployments. For production environments with high traffic, MySQL is recommended.' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ID Type Selection (common for all database types) -->
        <div class="border-t border-slate-700 pt-6 mt-6">
            <label class="block text-sm font-medium text-slate-300 mb-3">
                <?= __('database.id_type') ?? 'ID Type for Users and Entities' ?> <span class="text-red-400">*</span>
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- UUID Option -->
                <label class="relative flex items-start p-4 bg-slate-700/50 border-2 border-brand-500 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors" id="id-type-uuid-label">
                    <input
                        type="radio"
                        name="id_type"
                        value="uuid"
                        checked
                        class="sr-only"
                        onchange="document.querySelectorAll('[id^=id-type-]').forEach(l => l.classList.remove('border-brand-500')); document.getElementById('id-type-uuid-label').classList.add('border-brand-500');"
                    >
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">UUID v7</span>
                            <span class="px-2 py-0.5 text-xs bg-brand-600 text-white rounded"><?= __('database.recommended') ?? 'Recommended' ?></span>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.uuid_description') ?? 'Universal unique identifiers. More secure, scalable, and recommended for new installations.' ?>
                        </p>
                    </div>
                </label>

                <!-- Integer Option -->
                <label class="relative flex items-start p-4 bg-slate-700/50 border-2 border-slate-600 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors" id="id-type-integer-label">
                    <input
                        type="radio"
                        name="id_type"
                        value="integer"
                        class="sr-only"
                        onchange="document.querySelectorAll('[id^=id-type-]').forEach(l => l.classList.remove('border-brand-500')); document.getElementById('id-type-integer-label').classList.add('border-brand-500');"
                    >
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">Integer</span>
                            <span class="px-2 py-0.5 text-xs bg-slate-600 text-slate-300 rounded"><?= __('database.legacy') ?? 'Legacy' ?></span>
                        </div>
                        <p class="text-sm text-slate-400">
                            <?= __('database.integer_description') ?? 'Auto-incremental numeric IDs. Compatible with legacy system migrations.' ?>
                        </p>
                    </div>
                </label>
            </div>

            <p class="mt-2 text-xs text-amber-400">
                <strong><?= __('database.warning') ?? 'Important' ?>:</strong>
                <?= __('database.id_type_warning') ?? 'This configuration CANNOT be changed after installation.' ?>
            </p>
        </div>

    </form>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
