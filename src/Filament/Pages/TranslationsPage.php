<?php

namespace Aslnbxrz\FilamentTranslation\Filament\Pages;

use Aslnbxrz\SimpleTranslation\Models\AppText;
use Aslnbxrz\SimpleTranslation\Services\AppLanguageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class TranslationsPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationLabel = 'Translations';

    protected string $view = 'filament-simple-translation::translations-page';

    public static function getNavigationGroup(): string
    {
        return ___('Localization');
    }

    public static function getNavigationLabel(): string
    {
        return ___('Translations');
    }

    public function getTitle(): string
    {
        return ___('Translations');
    }

    public function getTableHeading(): string|Htmlable|null
    {
        return ___('Translations');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /** @var Collection<int, array{code:string,name:string}> */
    public Collection $languages;

    public string $scope;

    public function mount(): void
    {
        $this->scope = (string)Config::get('filament-simple-translation.default_scope', 'app');
        $this->languages = AppLanguageService::getLanguages();
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        return AppText::query()->with('translations')->where('scope', $this->scope);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')
                    ->copyable()
                    ->copyableState(fn(AppText $record) => $record->text)
                    ->label(___('Core / key'))
                    ->tooltip(fn(AppText $record) => $record->text)
                    ->limit(30)
                    ->searchable(),
                ...$this->getTranslateColumns(),
            ])
            ->filters([
                SelectFilter::make('scope')
                    ->label(___('Scope'))
                    ->selectablePlaceholder(false)
                    ->options(Config::get('filament-simple-translation.scopes', []))
                    ->default($this->scope)
                    ->native(false)
                    ->preload(),
                ...$this->getLanguageEmptyFilters()
            ], FiltersLayout::AboveContent)
            ->striped()
            ->emptyStateHeading(___('No translation keys found'))
            ->emptyStateDescription(___('Run scanner or add translation keys to see items.'));
    }

    private function getTranslateColumns(): array
    {
        return $this->languages->map(fn(array $language) => $this->getTranslateField($language))->all();
    }

    private function getTranslateField(array $language)
    {
        $locale = $language['code'];
        $name = $language['name'];
        return TextColumn::make("translations.$locale")
            ->default(fn(AppText $record) => $record->translations->firstWhere('lang_code', $locale)?->text ?? ' - ')
            ->limit(30)
            ->icon('heroicon-o-pencil-square')
            ->iconPosition(IconPosition::Before)
            ->iconColor(Color::Blue)
            ->label(($name ?? strtoupper($locale)) . " ($locale)")
            ->action(
                Action::make("edit-name.$locale")
                    ->schema([
                        TextInput::make('text')->required(),
                        Hidden::make('lang')->required(),
                    ])
                    ->fillForm(fn(?AppText $record) => ['text' => $record?->translations->firstWhere('lang_code', $locale)?->text, 'lang' => $locale])
                    ->action(function (AppText $record, $data) {
                        AppLanguageService::translate($record, $data['lang'], $data['text']);
                        AppLanguageService::generateTranslationsToStore($this->scope);
                    })
            );
    }

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
                        true: fn(Builder $q) => $q->whereHas(
                            'translations',
                            fn(Builder $t) => $t->where('lang_code', $locale)
                                ->whereNotNull('text')
                                ->where('text', '!=', '')
                        ),
                        false: fn(Builder $q) => $q->where(function (Builder $qq) use ($locale) {
                            $qq->whereDoesntHave('translations', fn(Builder $t) => $t->where('lang_code', $locale))
                                ->orWhereHas('translations', fn(Builder $t) => $t->where('lang_code', $locale)
                                    ->where(fn(Builder $w) => $w->whereNull('text')->orWhere('text', '=', '')));
                        }),
                    );
            })
            ->all();
    }

    private function getLanguageRadioFilters(): array
    {
        return $this->languages->map(function (array $language) {
            $locale = $language['code'];
            $label = ($language['name'] ?? strtoupper($locale)) . " ($locale)";

            return Filter::make("lang_$locale")
                ->label($label)
                ->schema([
                    Radio::make('state')
                        ->label($label)
                        ->options([
                            'all' => ___('All'),
                            'filled' => ___('Filled'),
                            'empty' => ___('Empty'),
                        ])
                        ->inline(false)
                        ->default('all'),
                ])
                ->indicateUsing(function (array $data) use ($label) {
                    return match ($data['state'] ?? 'all') {
                        'filled' => "$label: Filled",
                        'empty' => "$label: Empty",
                        default => null,
                    };
                })
                ->query(function (Builder $q, array $data) use ($locale) {
                    return match ($data['state'] ?? 'all') {
                        'filled' => $q->whereHas('translations', fn(Builder $t) => $t->where('lang_code', $locale)
                            ->whereNotNull('text')
                            ->where('text', '!=', '')
                        ),
                        'empty' => $q->where(function (Builder $qq) use ($locale) {
                            $qq->whereDoesntHave('translations', fn(Builder $t) => $t->where('lang_code', $locale)
                            )->orWhereHas('translations', function (Builder $t) use ($locale) {
                                $t->where('lang_code', $locale)
                                    ->where(function (Builder $w) {
                                        $w->whereNull('text')->orWhere('text', '');
                                    });
                            });
                        }),
                        default => $q,
                    };
                });
        })->all();
    }
}