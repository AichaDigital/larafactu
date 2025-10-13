<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Invoice Number')
                    ->required()
                    ->maxLength(255),

                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'email')
                    ->required()
                    ->searchable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('draft'),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'invoice' => 'Invoice',
                        'credit_note' => 'Credit Note',
                        'proforma' => 'Proforma',
                    ])
                    ->required()
                    ->default('invoice'),

                // Lara100: amounts stored as base-100 integers (€12.34 => 1234)
                TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->required()
                    ->helperText('Amount in base-100 format (1234 = €12.34)'),

                TextInput::make('tax_amount')
                    ->label('Tax Amount')
                    ->numeric()
                    ->required()
                    ->helperText('Amount in base-100 format (1234 = €12.34)'),

                TextInput::make('total')
                    ->label('Total')
                    ->numeric()
                    ->required()
                    ->helperText('Amount in base-100 format (1234 = €12.34)'),

                DatePicker::make('due_date')
                    ->label('Due Date'),

                DatePicker::make('paid_at')
                    ->label('Paid At'),
            ]);
    }
}
