<?php
declare(strict_types=1);

namespace LeG\Services;

class GameDataService
{
    public static function build(array $game, int $difficulty, array $components): array
    {
        $hw = array_values(array_filter($components, fn($c) => $c['category'] !== 'history'));
        switch ($game['kind']) {
            case 'drag_drop':  return self::assemble($difficulty, $hw);
            case 'identify':   return self::identify($difficulty, $hw);
            case 'match':      return self::match($difficulty, $hw);
            case 'cables':     return self::cables($difficulty, $components);
            case 'mini_quiz':  return self::miniQuiz($difficulty, $hw);
            case 'timeline':   return self::timeline($difficulty);
        }
        return ['rounds' => []];
    }

    private static function assemble(int $diff, array $components): array
    {
        $slotMap = [
            ['slot' => 'cpu_socket',   'label' => 'Socket CPU',       'slug' => 'cpu'],
            ['slot' => 'ram_slot',     'label' => 'Slot RAM (DIMM)',  'slug' => 'ram'],
            ['slot' => 'gpu_slot',     'label' => 'Slot PCIe x16',    'slug' => 'gpu'],
            ['slot' => 'm2_slot',      'label' => 'Slot M.2 (NVMe)',  'slug' => 'ssd'],
            ['slot' => 'sata_slot',    'label' => 'Port SATA',        'slug' => 'hdd'],
            ['slot' => 'psu_slot',     'label' => 'Conector ATX 24p', 'slug' => 'psu'],
            ['slot' => 'cooler_slot',  'label' => 'Montura cooler',   'slug' => 'cooler'],
        ];
        $needed = [3, 5, 7][$diff - 1] ?? 5;
        $slots  = array_slice($slotMap, 0, $needed);

        $bySlug = [];
        foreach ($components as $c) $bySlug[$c['slug']] = $c;

        $pieces = [];
        $validSlots = [];
        foreach ($slots as $s) {
            if (isset($bySlug[$s['slug']])) {
                $c = $bySlug[$s['slug']];
                $pieces[] = [
                    'slug' => $c['slug'],
                    'name' => $c['name'],
                    'icon' => $c['icon'],
                    'short_desc' => $c['short_desc'],
                ];
                $validSlots[] = $s;
            }
        }
        $slots = $validSlots;
        shuffle($pieces);

        return [
            'slots'  => $slots,
            'pieces' => $pieces,
            'points_per_slot' => 100,
            'time_limit' => [120, 90, 60][$diff - 1] ?? 90,
        ];
    }

    private static function identify(int $diff, array $components): array
    {
        $count = [4, 6, 9][$diff - 1] ?? 6;
        $opts  = [3, 4, 5][$diff - 1] ?? 4;
        shuffle($components);
        $rounds = [];
        foreach (array_slice($components, 0, $count) as $c) {
            $distractorsPool = array_values(array_filter($components, fn($x) => $x['slug'] !== $c['slug']));
            shuffle($distractorsPool);
            $choices = array_slice($distractorsPool, 0, $opts - 1);
            $choices[] = $c;
            shuffle($choices);
            $rounds[] = [
                'question' => $c['short_desc'],
                'icon'     => $c['icon'],
                'answer'   => $c['slug'],
                'choices'  => array_map(fn($x) => ['slug' => $x['slug'], 'name' => $x['name']], $choices),
            ];
        }
        return [
            'rounds' => $rounds,
            'points_per_correct' => 100,
            'time_limit' => [120, 90, 75][$diff - 1] ?? 90,
        ];
    }

    private static function match(int $diff, array $components): array
    {
        $count = [4, 6, 8][$diff - 1] ?? 6;
        shuffle($components);
        $picked = array_slice($components, 0, $count);

        $pairs = [];
        foreach ($picked as $c) {
            $specs = $c['specs'] ?? [];
            if (empty($specs)) continue;
            $key = array_rand($specs);
            $pairs[] = [
                'slug' => $c['slug'],
                'component_name' => $c['name'],
                'icon' => $c['icon'],
                'spec_label' => $key,
                'spec_value' => $specs[$key],
            ];
        }
        if (empty($pairs)) {
            foreach (array_slice($components, 0, $count) as $c) {
                $pairs[] = [
                    'slug' => $c['slug'],
                    'component_name' => $c['name'],
                    'icon' => $c['icon'],
                    'spec_label' => 'Tip',
                    'spec_value' => $c['short_desc'],
                ];
            }
        }
        $items = array_map(fn($p) => [
            'spec_text' => $p['spec_label'] . ': ' . $p['spec_value'],
            'answer'    => $p['slug'],
        ], $pairs);
        shuffle($items);
        $targets = array_map(fn($p) => [
            'slug' => $p['slug'],
            'name' => $p['component_name'],
            'icon' => $p['icon'],
        ], $pairs);
        shuffle($targets);

        return [
            'items'   => $items,
            'targets' => $targets,
            'points_per_correct' => 100,
            'time_limit' => [120, 90, 75][$diff - 1] ?? 90,
        ];
    }

    private static function cables(int $diff, array $components = []): array
    {
        $bySlug = [];
        foreach ($components as $c) $bySlug[$c['slug']] = $c['icon'] ?? 'tools.png';

        $all = [
            ['cable' => '24-pin ATX',    'target' => 'motherboard', 'label' => 'Placa de baza'],
            ['cable' => '8-pin EPS',     'target' => 'cpu',         'label' => 'Procesor (CPU)'],
            ['cable' => '8-pin PCIe',    'target' => 'gpu',         'label' => 'Placa grafica (GPU)'],
            ['cable' => 'SATA Power',    'target' => 'hdd',         'label' => 'HDD'],
            ['cable' => 'Molex 4-pin',   'target' => 'cooler',      'label' => 'Cooler'],
            ['cable' => 'SATA Power 2',  'target' => 'ssd',         'label' => 'SSD'],
        ];
        foreach ($all as &$entry) {
            $entry['icon'] = $bySlug[$entry['target']] ?? 'tools.png';
        }
        unset($entry);

        $count = [3, 5, 6][$diff - 1] ?? 5;
        $rounds = array_slice($all, 0, $count);
        $targets = $rounds;
        shuffle($targets);

        return [
            'cables'  => $rounds,
            'targets' => $targets,
            'points_per_correct' => 100,
            'time_limit' => [120, 90, 60][$diff - 1] ?? 90,
        ];
    }

    private static function timeline(int $diff): array
    {
        $events = [
            ['id' =>  1, 'label' => 'Abacul',                  'year' => -2000,
             'hint' => 'Primul instrument de calcul, folosit in Mesopotamia si China.'],
            ['id' =>  2, 'label' => 'Masina lui Babbage',       'year' => 1822,
             'hint' => 'Primul concept de calculator mecanic, proiectat de Charles Babbage.'],
            ['id' =>  3, 'label' => 'ENIAC',                    'year' => 1945,
             'hint' => 'Primul calculator electronic de uz general, cantarind 27 de tone.'],
            ['id' =>  4, 'label' => 'Tranzistorul Bell Labs',   'year' => 1947,
             'hint' => 'Inventat de Shockley, Bardeen si Brattain — a inlocuit tubul electronic.'],
            ['id' =>  5, 'label' => 'Primul hard disk (IBM 350)', 'year' => 1956,
             'hint' => 'Capacitate de 5 MB, dimensiunea unui frigider. Inchiriat cu 3.200$/luna.'],
            ['id' =>  6, 'label' => 'Legea lui Moore',          'year' => 1965,
             'hint' => 'Numarul de tranzistori se dubleaza aproximativ la fiecare 2 ani.'],
            ['id' =>  7, 'label' => 'Intel 4004',               'year' => 1971,
             'hint' => 'Primul microprocesor comercial intr-un singur chip, cu 2.300 de tranzistori.'],
            ['id' =>  8, 'label' => 'Intel 8086',               'year' => 1978,
             'hint' => 'A pus bazele arhitecturii x86 folosita si astazi in calculatoarele personale.'],
            ['id' =>  9, 'label' => 'IBM PC',                   'year' => 1981,
             'hint' => 'Primul calculator personal IBM cu procesor Intel 8088 si sistem DOS.'],
            ['id' => 10, 'label' => 'Windows 95',               'year' => 1995,
             'hint' => 'A introdus meniul Start, bara de taskuri si Plug & Play.'],
            ['id' => 11, 'label' => 'USB 1.0',                  'year' => 1996,
             'hint' => 'Standard universal care a inlocuit porturile seriale si paralele.'],
            ['id' => 12, 'label' => 'Procesorul Dual-Core',     'year' => 2005,
             'hint' => 'Intel Pentium D — primul CPU dual-core pentru consumatori.'],
        ];
        $count  = [4, 7, 10][$diff - 1] ?? 7;
        $slice  = array_slice($events, 0, $count);
        $correct = array_column($slice, 'id');
        $shuffled = $slice; shuffle($shuffled);
        $display = array_map(fn($e) => [
            'id' => $e['id'], 'label' => $e['label'], 'hint' => $e['hint'],
        ], $shuffled);
        return [
            'events'            => $display,
            'correct_order'     => $correct,
            'points_per_correct'=> 100,
            'time_limit'        => [150, 180, 240][$diff - 1] ?? 180,
        ];
    }

    private static function miniQuiz(int $diff, array $components): array
    {
        $count = [5, 8, 12][$diff - 1] ?? 8;
        shuffle($components);
        $rounds = [];
        foreach (array_slice($components, 0, $count) as $c) {
            $distractors = array_values(array_filter($components, fn($x) => $x['slug'] !== $c['slug']));
            shuffle($distractors);
            $opts = array_slice($distractors, 0, 3);
            $opts[] = $c;
            shuffle($opts);
            $rounds[] = [
                'prompt'  => $c['short_desc'],
                'answer'  => $c['slug'],
                'choices' => array_map(fn($x) => ['slug' => $x['slug'], 'name' => $x['name']], $opts),
            ];
        }
        return [
            'rounds' => $rounds,
            'points_per_correct' => 50,
            'time_per_round' => [6, 4, 3][$diff - 1] ?? 4,
        ];
    }
}
