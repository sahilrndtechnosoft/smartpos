<?php

use App\Http\Controllers\RunKeyboardShortcutController;
use App\Http\Controllers\PrintDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('harsh/adminpov/print')->name('print.')->group(function (): void {
    Route::get('orders/{order}/invoice', [PrintDocumentController::class, 'salesOrderInvoice'])
        ->name('orders.invoice');

    Route::get('inventories/{inventory}/invoice', [PrintDocumentController::class, 'inventoryReceipt'])
        ->name('inventories.invoice');
});

Route::middleware(['auth'])->group(function (): void {
    Route::post('harsh/adminpov/keyboard-shortcuts/{keyboardShortcut}/run', RunKeyboardShortcutController::class)
        ->name('keyboard-shortcuts.run');
});
