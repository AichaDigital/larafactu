<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\LegalEntityType;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\DelegatedUsersRelationManager;
use App\Filament\Resources\Users\RelationManagers\TaxProfilesRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.groups.internal');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.user.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.user.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.user.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic info
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.user.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('email')
                    ->label(__('filament.user.fields.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('password')
                    ->label(__('filament.user.fields.password'))
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->helperText(__('filament.user.fields.password_helper'))
                    ->columnSpan(1),

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('filament.user.fields.email_verified_at'))
                    ->native(false)
                    ->columnSpan(1),

                // Billing info
                Forms\Components\TextInput::make('display_name')
                    ->label(__('filament.user.fields.display_name'))
                    ->maxLength(255)
                    ->helperText(__('filament.user.fields.display_name_helper'))
                    ->columnSpan(1),

                Forms\Components\Select::make('legal_entity_type_code')
                    ->label(__('filament.user.fields.legal_entity_type'))
                    ->options(
                        LegalEntityType::active()
                            ->ordered()
                            ->get()
                            ->mapWithKeys(fn (LegalEntityType $type) => [
                                $type->code => $type->formatted_name,
                            ])
                    )
                    ->searchable()
                    ->preload()
                    ->columnSpan(1),

                Forms\Components\Select::make('relationship_type')
                    ->label(__('filament.user.fields.relationship_type'))
                    ->options(UserRelationshipType::class)
                    ->default(UserRelationshipType::DIRECT)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($set, $state) {
                        if ($state === UserRelationshipType::DIRECT->value || $state === UserRelationshipType::DIRECT) {
                            $set('parent_user_id', null);
                        }
                    })
                    ->columnSpan(1),

                Forms\Components\Select::make('parent_user_id')
                    ->label(__('filament.user.fields.parent_user'))
                    ->relationship(
                        'parentUser',
                        'name',
                        fn (Builder $query) => $query->whereNull('parent_user_id')
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get): bool => $get('relationship_type') === UserRelationshipType::DELEGATED->value
                        || $get('relationship_type') === UserRelationshipType::DELEGATED
                    )
                    ->required(fn ($get): bool => $get('relationship_type') === UserRelationshipType::DELEGATED->value
                        || $get('relationship_type') === UserRelationshipType::DELEGATED
                    )
                    ->helperText(__('filament.user.fields.parent_user_helper'))
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.user.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): ?string => $record->display_name),

                TextColumn::make('email')
                    ->label(__('filament.user.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('relationship_type')
                    ->label(__('filament.user.fields.relationship_type'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('parentUser.name')
                    ->label(__('filament.user.fields.parent_user'))
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('legalEntityType.name')
                    ->label(__('filament.user.fields.legal_entity_type'))
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('delegated_users_count')
                    ->label(__('filament.user.table.delegated_count'))
                    ->counts('delegatedUsers')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('email_verified_at')
                    ->label(__('filament.user.table.verified'))
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament.user.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('relationship_type')
                    ->label(__('filament.user.filters.relationship_type'))
                    ->options(UserRelationshipType::class),

                SelectFilter::make('legal_entity_type_code')
                    ->label(__('filament.user.filters.legal_entity_type'))
                    ->options(
                        LegalEntityType::active()
                            ->ordered()
                            ->pluck('name', 'code')
                    ),

                TernaryFilter::make('has_delegated')
                    ->label(__('filament.user.filters.has_delegated'))
                    ->placeholder(__('filament.user.filters.has_delegated_all'))
                    ->trueLabel(__('filament.user.filters.has_delegated_true'))
                    ->falseLabel(__('filament.user.filters.has_delegated_false'))
                    ->queries(
                        true: fn (Builder $query) => $query->has('delegatedUsers'),
                        false: fn (Builder $query) => $query->doesntHave('delegatedUsers'),
                    ),

                TernaryFilter::make('email_verified_at')
                    ->label(__('filament.user.filters.verified'))
                    ->placeholder(__('filament.user.filters.verified_all'))
                    ->trueLabel(__('filament.user.filters.verified_true'))
                    ->falseLabel(__('filament.user.filters.verified_false'))
                    ->nullable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DelegatedUsersRelationManager::class,
            TaxProfilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
