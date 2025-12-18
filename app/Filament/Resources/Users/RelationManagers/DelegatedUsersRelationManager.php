<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use AichaDigital\Larabill\Enums\UserRelationshipType;
use AichaDigital\Larabill\Models\LegalEntityType;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DelegatedUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'delegatedUsers';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.user.relations.delegated_users');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.user.fields.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label(__('filament.user.fields.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: User::class, ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label(__('filament.user.fields.password'))
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8),

                Forms\Components\TextInput::make('display_name')
                    ->label(__('filament.user.fields.display_name'))
                    ->maxLength(255),

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
                    ->preload(),

                Forms\Components\Hidden::make('relationship_type')
                    ->default(UserRelationshipType::DELEGATED),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
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
                    ->copyable(),

                TextColumn::make('legalEntityType.name')
                    ->label(__('filament.user.fields.legal_entity_type'))
                    ->placeholder('-'),

                IconColumn::make('email_verified_at')
                    ->label(__('filament.user.table.verified'))
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null),

                TextColumn::make('created_at')
                    ->label(__('filament.user.fields.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['relationship_type'] = UserRelationshipType::DELEGATED;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
