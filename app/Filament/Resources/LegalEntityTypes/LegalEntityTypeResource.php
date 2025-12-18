<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalEntityTypes;

use AichaDigital\Larabill\Models\LegalEntityType;
use App\Filament\Resources\LegalEntityTypes\Pages\ManageLegalEntityTypes;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Livewire\Component;
use Matriphe\Bendera\BenderaFacade as Bendera;

class LegalEntityTypeResource extends Resource
{
    use Translatable;

    protected static ?string $model = LegalEntityType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 20;

    /**
     * Locale to flag/name mapping.
     *
     * @var array<string, array{flag: string, name: string}>
     */
    protected static array $localeLabels = [
        'es' => ['flag' => 'es', 'name' => 'Español'],
        'en' => ['flag' => 'gb', 'name' => 'English'],
    ];

    /**
     * @return array<string>
     */
    public static function getTranslatableLocales(): array
    {
        return ['es', 'en'];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.groups.internal');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.legal_entity_type.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.legal_entity_type.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.legal_entity_type.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Placeholder::make('locale_indicator')
                    ->label('')
                    ->content(function (Component $livewire): HtmlString {
                        $locale = $livewire->activeLocale ?? 'es';
                        $config = static::$localeLabels[$locale] ?? static::$localeLabels['es'];

                        $flag = Bendera::emoji($config['flag']) ?? '';

                        return new HtmlString(
                            '<div class="flex items-center gap-2 text-lg font-medium text-primary-600 dark:text-primary-400">'
                            .'<span class="text-2xl">'.$flag.'</span>'
                            .'<span>'.__('filament.legal_entity_type.editing_locale', ['locale' => $config['name']]).'</span>'
                            .'</div>'
                        );
                    })
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('code')
                    ->label(__('filament.legal_entity_type.fields.code'))
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('LIMITED_COMPANY'),

                Forms\Components\TextInput::make('name')
                    ->label(__('filament.legal_entity_type.fields.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('abbreviation')
                    ->label(__('filament.legal_entity_type.fields.abbreviation'))
                    ->maxLength(20),

                Forms\Components\TextInput::make('country_code')
                    ->label(__('filament.legal_entity_type.fields.country_code'))
                    ->required()
                    ->maxLength(2)
                    ->default('ES'),

                Forms\Components\Toggle::make('requires_tax_id')
                    ->label(__('filament.legal_entity_type.fields.requires_tax_id'))
                    ->default(true),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament.legal_entity_type.fields.is_active'))
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('filament.legal_entity_type.fields.sort_order'))
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description')
                    ->label(__('filament.legal_entity_type.fields.description'))
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament.legal_entity_type.fields.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.legal_entity_type.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('abbreviation')
                    ->label(__('filament.legal_entity_type.fields.abbreviation'))
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('country_code')
                    ->label(__('filament.legal_entity_type.fields.country_code'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.legal_entity_type.fields.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('filament.legal_entity_type.fields.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament.legal_entity_type.filters.is_active'))
                    ->placeholder(__('filament.legal_entity_type.filters.all'))
                    ->trueLabel(__('filament.legal_entity_type.filters.active_only'))
                    ->falseLabel(__('filament.legal_entity_type.filters.inactive_only')),

                Tables\Filters\SelectFilter::make('country_code')
                    ->label(__('filament.legal_entity_type.filters.country'))
                    ->options([
                        'ES' => 'España',
                        'PT' => 'Portugal',
                        'FR' => 'Francia',
                        'DE' => 'Alemania',
                        'IT' => 'Italia',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLegalEntityTypes::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) LegalEntityType::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
