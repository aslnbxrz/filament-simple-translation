<?php

namespace Aslnbxrz\FilamentSimpleTranslation\Filament\Pages;

use Aslnbxrz\SimpleTranslation\Models\AppText;
use Aslnbxrz\SimpleTranslation\Services\AppLanguageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;

/**
 * Filament page for managing translations.
 *
 * Features:
 * - Shows translation keys (AppText) with per-locale columns.
 * - Inline edit action writes to DB and regenerates per-scope JSON for that locale.
 * - Header quick buttons control native Filament filters (no custom query mutation).
 */
class TranslationsPage extends Page implements HasTable
{
    use InteractsWithTable;

    /** Navigation icon */
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedLanguage;

    /** Blade view used to render the page */
    protected string $view = 'filament-simple-translation::page';

    /** @return string Navigation group label */
    public static function getNavigationGroup(): string
    {
        return ___('Localization');
    }

    /** @return string Navigation label */
    public static function getNavigationLabel(): string
    {
        return ___('Translations');
    }

    /** @return string Page title */
    public function getTitle(): string
    {
        return ___('Translations');
    }

    /** @return string|Htmlable|null Table heading */
    public function getTableHeading(): string|Htmlable|null
    {
        return ___('Translations');
    }

    /** Control navigation visibility */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /** @var Collection<int, array{code:string,name:string}> Available languages */
    public Collection $languages;

    /** @var string Current scope (e.g., "app", "exceptions") */
    public string $scope;

    /**
     * Livewire mount hook: load default scope & languages.
     */
    public function mount(): void
    {
        $this->scope = (string)Config::get('simple-translation.default_scope', 'app');
        $this->languages = AppLanguageService::getLanguages();
    }

    /**
     * Base Eloquent query for the table.
     * Must return an Eloquent Builder (not Relation / Query\Builder).
     */
    protected function getTableQuery(): Builder
    {
        return AppText::query()->with('translations');
    }

    /**
     * Build the Filament table schema: columns, filters, etc.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')
                    ->label(___('Core / key'))
                    ->copyable()
                    ->copyableState(fn(AppText $r) => $r->text)
                    ->tooltip(fn(AppText $r) => $r->text)
                    ->limit(30)
                    ->searchable(), // <-- global search shu ustundan ishlaydi

                ...$this->getTranslateColumns(),
            ])
            ->persistFiltersInSession()
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filters([
                SelectFilter::make('scope')
                    ->label(___('Scope'))
                    ->selectablePlaceholder(false)
                    ->options(Config::get('simple-translation.available_scopes', [
                        Config::get('simple-translation.default_scope', 'app'),
                    ]))
                    ->default($this->scope)
                    ->native(false)
                    ->preload(),
                ...$this->getLanguageEmptyFilters()
            ])
            ->striped()
            ->emptyStateHeading(___('No translation keys found'))
            ->emptyStateDescription(___('Run scanner or add translation keys to see items.'));
    }

    /**
     * Build all per-locale columns with header quick buttons that toggle native filters.
     *
     * @return array<int, TextColumn>
     */
    private function getTranslateColumns(): array
    {
        return $this->languages
            ->map(fn(array $language) => $this->getTranslateField($language))
            ->all();
    }

    /**
     * Single per-locale column: shows value, provides inline edit action,
     * and renders header "All/Has/No" buttons that set Filament's own filter state.
     *
     * @param array{code:string,name:string} $language
     */
    private function getTranslateField(array $language): TextColumn
    {
        $locale = $language['code'];
        $name = $language['name'];
        $label = ($name ?? strtoupper($locale)) . " ($locale)";

//        $headerHtml = <<<HTML
//<span
//  x-data="{
//    v: \$wire.entangle('tableFilters.has_{$locale}.value').live,
//    isAll() { return this.v === null || this.v === undefined || this.v === '' },
//    isHas() { return this.v === true  || this.v === 1 || this.v === '1' || this.v === 'true' },
//    isNo()  { return this.v === false || this.v === 0 || this.v === '0' || this.v === 'false' },
//  }"
//  class="block w-full leading-tight"
//>
//  <!-- label tepada -->
//  <span class="block font-medium mb-1">{$label}</span>
//
//  <!-- badge'lar pastda (alohida qator) -->
//  <span class="block">
//    <span class="flex flex-wrap gap-1 py-1">
//      <button
//        type="button"
//        class="fi-badge fi-badge-label px-2 py-0.5 text-xs rounded-full border transition mx-1"
//        :class="isAll()
//          ? 'bg-primary-600 text-white border-primary-600'
//          : 'bg-transparent text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
//        x-on:click.stop="v = null; \$wire.dispatch('refresh')"
//      >All</button>
//
//      <button
//        type="button"
//        class="fi-badge fi-badge-label px-2 py-0.5 text-xs rounded-full border transition mx-1"
//        :class="isHas()
//          ? 'bg-primary-600 text-white border-primary-600'
//          : 'bg-transparent text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
//        x-on:click.stop="v = true; \$wire.dispatch('refresh')"
//      >Has</button>
//
//      <button
//        type="button"
//        class="fi-badge fi-badge-label px-2 py-0.5 text-xs rounded-full border transition mx-1"
//        :class="isNo()
//          ? 'bg-primary-600 text-white border-primary-600'
//          : 'bg-transparent text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
//        x-on:click.stop="v = false; \$wire.dispatch('refresh')"
//      >No</button>
//    </span>
//  </span>
//</span>
//HTML;

        $headerHtml = $label;

        return TextColumn::make("translations.$locale")
            ->label(new HtmlString($headerHtml))
            ->default(fn(AppText $record) => $record->translations->firstWhere('lang_code', $locale)?->text ?? ' - ')
            ->limit(30)
            ->icon('heroicon-o-pencil-square')
            ->iconPosition(IconPosition::Before)
            ->iconColor(Color::Blue)
            ->action(
                Action::make("edit-name.$locale")
                    ->schema([
                        TextInput::make('text')->required(),
                        Hidden::make('lang')->required(),
                    ])
                    ->fillForm(fn(?AppText $record) => [
                        'text' => $record?->translations->firstWhere('lang_code', $locale)?->text,
                        'lang' => $locale,
                    ])
                    ->action(function (AppText $record, array $data) {
                        // Save to DB
                        $record->translate((string)$data['lang'], (string)$data['text']);
                        // Rebuild only affected scope+locale JSON
                        AppLanguageService::exportScope($record->scope, [(string)$data['lang']]);
                        // Refresh table
                        $this->dispatch('refresh')->self();
                    })
            );
    }

    /**
     * Build native Ternary filters (exists / not exists) for each locale.
     * These are controlled by header buttons via Livewire state.
     *
     * @return array<int, TernaryFilter>
     */
    private function getLanguageEmptyFilters(): array
    {
        return $this->languages
            ->map(function (array $language) {
                $locale = $language['code'];

                return TernaryFilter::make("has_$locale")
                    ->label(___('Translation exists?') . " ($locale)")
                    ->native(false)
                    ->trueLabel(___('Has translation'))
                    ->falseLabel(___('No translation'))
                    ->queries(
                        true: fn(Builder $q) => $q->whereHas('translations', fn(Builder $t) => $t
                            ->where('lang_code', $locale)
                            ->whereNotNull('text')
                            ->where('text', '!=', '')
                        ),
                        false: fn(Builder $q) => $q->where(function (Builder $qq) use ($locale) {
                            $qq->whereDoesntHave('translations', fn(Builder $t) => $t->where('lang_code', $locale))
                                ->orWhereHas('translations', fn(Builder $t) => $t->where('lang_code', $locale)
                                    ->where(fn(Builder $w) => $w->whereNull('text')->orWhere('text', ''))
                                );
                        }),
                    );
            })
            ->all();
    }
}