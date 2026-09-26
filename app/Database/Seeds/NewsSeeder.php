<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Sample university news: unique titles, mixed length, all visibility states.
 * Dates sit around late September 2026 so the listing looks current.
 */
class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('news')->truncate();

        foreach ($this->rows() as $index => $row) {
            $stamp             = sprintf('2026-09-%02d %02d:%02d:00', 4 + ($index % 22), 8 + ($index % 10), ($index * 7) % 60);
            $row['created_at'] = $stamp;
            $row['updated_at'] = $stamp;
            $this->db->table('news')->insert($row);
        }
    }

    /**
     * @return list<array{title: string, content: string, visible_from: string, visible_to: string|null}>
     */
    private function rows(): array
    {
        return [
            [
                'title'        => 'Úprava otevírací doby knihovny',
                'content'      => $this->document(
                    [
                        'Od tohoto týdne má ústřední knihovna prodloužený večerní provoz. Studovny v přízemí zůstávají otevřené do 21:00, badatelna do 19:00.',
                        'Víkendový režim se nemění. Vstup je možný s platným průkazem studenta nebo zaměstnance.',
                    ],
                    ['Pondělí–čtvrtek 8:00–21:00', 'Pátek 8:00–18:00', 'Sobota 9:00–14:00'],
                ),
                'visible_from' => '2026-09-26 07:40:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Přesun výuky z učebny A3',
                'content'      => $this->document([
                    'Z důvodu malování se přednášky plánované v učebně A3 přesouvají do posluchárny C1. Změna platí do odvolání a je zapsaná v rozvrhu.',
                ]),
                'visible_from' => '2026-09-25 16:10:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Přihlášky na podzimní jazykový kurz',
                'content'      => $this->document(
                    [
                        'Centrum jazyků otevírá večerní kurz akademické angličtiny pro studenty všech fakult. Výuka probíhá jednou týdně, skupiny se dělí podle vstupního testu.',
                        'Kapacita je omezená. Přihlášky se podávají přes informační systém, poplatek se hradí až po potvrzení místa.',
                    ],
                    ['Úroveň B1 a B2', '12 týdnů, 90 minut', 'Start v týdnu od 6. října'],
                ),
                'visible_from' => '2026-09-25 09:05:00',
                'visible_to'   => '2026-10-20 23:59:00',
            ],
            [
                'title'        => 'Uzavření parkoviště u koleje Jih',
                'content'      => $this->document([
                    'Parkoviště u koleje Jih bude tři dny uzavřené kvůli výměně povrchu. Náhradní stání je vyznačené u sportovní haly.',
                ]),
                'visible_from' => '2026-09-24 18:20:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Nové konzultační hodiny děkanátu',
                'content'      => $this->document([
                    'Studijní oddělení mění pořadí návštěv. Osobní jednání je možné po rezervaci termínu, bez objednání jen ve středu dopoledne.',
                    'Doklady k uznání předmětů zasílejte nejprve elektronicky. Na přepážce se řeší jen doplnění chybějících razítek.',
                ]),
                'visible_from' => '2026-09-24 08:15:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Sbírka pro studentský spolek',
                'content'      => $this->document(
                    [
                        'Ve vstupní hale kampusu probíhá sbírka na vybavení klubovny studentského spolku. Lze přispět hotově i převodem.',
                        'Za každý dar nad 200 Kč je připravený drobný suvenýr. Výtěžek se zveřejní na nástěnce spolku.',
                    ],
                    ['Kasička u recepce', 'QR platba na letáku', 'Ukončení sbírky 10. října'],
                ),
                'visible_from' => '2026-09-23 12:50:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Změna dodavatele menzy',
                'content'      => $this->document([
                    'Od příštího týdne vaří v hlavní menze nový provozovatel. Stávající čipové karty zůstávají v platnosti, ceny obědů se nemění.',
                    'Alergeny budou nově vyznačené i u výdeje. Připomínky ke skladbě jídelníčku lze poslat na <a href="mailto:stravovani@example.com">stravovani@example.com</a>.',
                ]),
                'visible_from' => '2026-09-22 11:00:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Noční studovna v zkouškovém období',
                'content'      => $this->document([
                    'Před zkouškovým obdobím se otevře noční studovna v budově F. Místa je nutné rezervovat den předem, vstup po 22:00 jen s kartou.',
                ]),
                'visible_from' => '2026-09-21 19:30:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Výluka výtahu ve staré budově',
                'content'      => $this->document([
                    'Servis výtahu ve staré budově potrvá do pátku. Osoby se sníženou pohyblivostí mohou využít bezbariérový vstup z dvora a výtah v přístavbě.',
                ]),
                'visible_from' => '2026-09-20 07:05:00',
                'visible_to'   => '2026-10-03 20:00:00',
            ],
            [
                'title'        => 'Workshop citací a práce se zdroji',
                'content'      => $this->document(
                    [
                        'Knihovna pořádá praktický workshop k citacím v závěrečných pracích. Účastníci si na vlastním textu vyzkouší citační manažer i kontrolu podobnosti.',
                        'S sebou notebook. Přihlášení je povinné, volná místa se uvolní den před akcí.',
                    ],
                    ['Úterý 14:00, učebna K2', 'Středa 9:30, učebna K2', 'Maximálně 18 osob ve skupině'],
                    true,
                ),
                'visible_from' => '2026-09-19 10:25:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Aktualizace Wi-Fi v kolejích',
                'content'      => $this->document([
                    'V kolejích Sever a Západ probíhá výměna přístupových bodů. Krátké výpadky sítě se mohou objevit vždy mezi 10:00 a 11:00.',
                    'Po dokončení se zařízení znovu připojí automaticky. Pokud se síť eduroam nepřihlásí, pomůže správa kolejí na recepci.',
                ]),
                'visible_from' => '2026-09-18 08:40:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Svoz elektroodpadu z laboratoří',
                'content'      => $this->document([
                    'Technické oddělení svozuje vysloužilé počítače, monitory a měřicí přístroje. Položky označte inventárním číslem a nechte je u vchodu laboratoře do 15:00.',
                ]),
                'visible_from' => '2026-09-17 13:15:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Zápis volitelných předmětů druhého kola',
                'content'      => $this->document(
                    [
                        'Druhé kolo zápisu volitelných předmětů začíná v pátek v 8:00. Přednost mají studenti, kteří v prvním kole nezískali žádný seminář.',
                        'Kapacity se uvolňují postupně. Stížnosti na kolize rozvrhu řeší studijní referentka daného programu, ne vyučující předmětu.',
                    ],
                    ['Informační systém → Zápis', 'Potvrzení e-mailem do hodiny', 'Uzávěrka v neděli ve 22:00'],
                ),
                'visible_from' => '2026-09-16 07:50:00',
                'visible_to'   => '2026-10-12 22:00:00',
            ],
            [
                'title'        => 'Oprava osvětlení na hřišti',
                'content'      => $this->document([
                    'Večerní tréninky na venkovním hřišti jsou na dva dny zrušené. Světla se mění za úspornější výbojky, provoz se obnoví po kolaudaci.',
                ]),
                'visible_from' => '2026-09-14 17:00:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Výzva k odevzdání průkazek hostů',
                'content'      => $this->document([
                    'Dočasné průkazky vydané na začátku semestru je nutné vrátit na vrátnici hlavní budovy. Po termínu se účtuje manipulační poplatek.',
                    'Ztrátu nahlaste písemně. Nový průkaz se tiskne až po potvrzení totožnosti na studijním oddělení.',
                ]),
                'visible_from' => '2026-09-12 09:35:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Setkání se zahraničními studenty',
                'content'      => $this->document(
                    [
                        'Mezinárodní oddělení zve na neformální setkání v aule. Program je v angličtině, občerstvení zajištěné.',
                        'Tlumočení do češtiny nebude. Dotazy k vízům řešte zvlášť na úředních hodinách, ne během akce.',
                    ],
                    ['Středa 17:00, aula', 'Registrace do úterý večer', 'Kontakt: <a href="mailto:incoming@example.com">incoming@example.com</a>'],
                ),
                'visible_from' => '2026-09-27 08:00:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Cvičný poplach v kampusu',
                'content'      => $this->document([
                    'V pondělí dopoledne proběhne cvičný požární poplach. Evakuace se týká budov B a C, výuka se po kontrole osob vrátí do původních učeben.',
                    'Nekompletní docházka z cvičení se neomlouvá. Osoby s omezenou pohyblivostí čekají u určeného výtahu s pedagogem.',
                ]),
                'visible_from' => '2026-09-28 10:20:00',
                'visible_to'   => '2026-09-28 16:00:00',
            ],
            [
                'title'        => 'Říjnový den s absolventy',
                'content'      => $this->document(
                    [
                        'Fakulta zve studenty posledních ročníků na odpoledne s absolventy z praxe. Krátké přednášky střídají stoly s dotazy v chodbě u auly.',
                        'Účast je dobrovolná, prezence se zapisuje kvůli občerstvení. Fotografie z akce mohou být použité ve výroční zprávě.',
                    ],
                    ['Zahájení ve 13:00', 'Tři tematické bloky', 'Závěr v 16:30 u bufetu'],
                    true,
                ),
                'visible_from' => '2026-09-30 07:30:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Uzávěrka žádostí o kolej na letní semestr',
                'content'      => $this->document([
                    'Žádosti o kolej na letní semestr se podávají pouze elektronicky. Papírové formuláře se nepřijímají.',
                    'O pořadí rozhoduje vzdálenost bydliště a prospěch, ne datum odeslání. Výsledky přijdou do školní pošty.',
                ]),
                'visible_from' => '2026-10-02 09:00:00',
                'visible_to'   => '2026-10-31 23:59:00',
            ],
            [
                'title'        => 'Sváteční provoz rektorátu',
                'content'      => $this->document([
                    'Na státní svátek bude rektorát uzavřený. Podatelna přijme zásilky až následující pracovní den, elektronická podání běží bez omezení.',
                ]),
                'visible_from' => '2026-10-06 12:00:00',
                'visible_to'   => null,
            ],
            [
                'title'        => 'Zápis do tělesné výchovy',
                'content'      => $this->document([
                    'Zápis do hodin tělesné výchovy už skončil. Dodatečné přesuny mezi skupinami se neprovádějí, volná místa se otevřou až v příštím semestru.',
                    'Omluvenky ze zdravotních důvodů dokládejte na katedru TV, ne vyučujícímu konkrétní skupiny.',
                ]),
                'visible_from' => '2026-09-01 08:00:00',
                'visible_to'   => '2026-09-20 18:00:00',
            ],
            [
                'title'        => 'Výdej indexů prvákům',
                'content'      => $this->document([
                    'Výdej studijních indexů pro první ročník je uzavřený. Kdo si doklad nevyzvedl, počká na náhradní termín v říjnu, který studijní oddělení zveřejní zvlášť.',
                ]),
                'visible_from' => '2026-09-03 09:10:00',
                'visible_to'   => '2026-09-18 16:00:00',
            ],
            [
                'title'        => 'Hlášení závad po nastěhování na kolej',
                'content'      => $this->document([
                    'Termín pro nahlášení závad po nastěhování na kolej uplynul. Pozdější oznámení se posuzují jako škoda způsobená během bydlení.',
                    'Fotografie stavu pokoje uložené v aplikaci kolejí slouží jako podklad při vystěhování.',
                ]),
                'visible_from' => '2026-09-06 14:00:00',
                'visible_to'   => '2026-09-22 12:00:00',
            ],
            [
                'title'        => 'Úvodní přednáška rektora',
                'content'      => $this->document([
                    'Záznam úvodní přednášky rektora je k dispozici v e-learningu. Osobní účast už není možná, aula se vrátila do běžného rozvrhu.',
                    'Dotazy položené v sále se zodpoví v souhrnném dokumentu, který vyjde v univerzitním zpravodaji.',
                ]),
                'visible_from' => '2026-09-09 17:45:00',
                'visible_to'   => '2026-09-24 20:00:00',
            ],
            [
                'title'        => 'Prázdninová půjčovna kol',
                'content'      => $this->document([
                    'Sezónní půjčovna kol u sportovního areálu ukončila provoz. Návrat posledních výpůjček byl možný do poloviny září, zálohy se vracely na účet.',
                    'Poškozená kola se řeší se správou areálu. Nová sezóna se plánuje až na jaro.',
                ]),
                'visible_from' => '2026-08-28 10:00:00',
                'visible_to'   => '2026-09-15 17:00:00',
            ],
        ];
    }

    /**
     * @param list<string> $paragraphs
     * @param list<string> $listItems
     */
    private function document(array $paragraphs, array $listItems = [], bool $ordered = false): string
    {
        $blocks = [];

        foreach ($paragraphs as $paragraph) {
            if ($paragraph === '') {
                continue;
            }

            $blocks[] = [
                'type' => 'paragraph',
                'data' => ['text' => $paragraph],
            ];
        }

        if ($listItems !== []) {
            $blocks[] = [
                'type' => 'list',
                'data' => [
                    'style' => $ordered ? 'ordered' : 'unordered',
                    'items' => array_map(static fn (string $item): array => [
                        'content' => $item,
                        'items'   => [],
                    ], $listItems),
                ],
            ];
        }

        return json_encode([
            'time'    => 1_725_000_000_000,
            'blocks'  => $blocks,
            'version' => '2.30.7',
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
