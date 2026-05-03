<?php
declare(strict_types=1);
/**
 * itemsinpage_class3.php РІР‚вЂќ Р СџР В°Р С–Р С‘Р Р…Р В°РЎвЂ Р С‘РЎРЏ
 * PHP 8.x: РЎРѓРЎвЂљРЎР‚Р С•Р С–Р В°РЎРЏ РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ, readonly-РЎРѓР Р†Р С•Р в„–РЎРѓРЎвЂљР Р†Р В°, Р С‘Р СР ВµР Р…Р С•Р Р†Р В°Р Р…Р Р…РЎвЂ№Р Вµ Р В°РЎР‚Р С–РЎС“Р СР ВµР Р…РЎвЂљРЎвЂ№
 */

declare(strict_types=1);

class Itemsinpage
{
    private const DEFAULT_PER_PAGE = 15;
    private const LINK_COUNT       = 25; // Р Р…Р ВµРЎвЂЎРЎвЂРЎвЂљР Р…Р С•Р Вµ!
    private const PER_PAGE_START   = 10;
    private const PER_PAGE_END     = 25;
    private const PER_PAGE_STEP    = 5;

    private int   $inpage;
    private int   $page;
    private int   $totalpages;
    protected array $res = [];

    public function __construct(int $total)
    {
        $this->page   = max(0, (int)($_GET['page'] ?? 0));
        $this->inpage = self::DEFAULT_PER_PAGE;

        // Р вЂ”Р В°Р С—Р С‘РЎРѓРЎвЂ№Р Р†Р В°Р ВµР С Р С•Р В±РЎР‚Р В°РЎвЂљР Р…Р С• Р Р† $_GET Р Т‘Р В»РЎРЏ РЎРѓР С•Р Р†Р СР ВµРЎРѓРЎвЂљР С‘Р СР С•РЎРѓРЎвЂљР С‘ РЎРѓ РЎв‚¬Р В°Р В±Р В»Р С•Р Р…Р В°Р СР С‘
        $_GET['page']  = $this->page;
        $_GET['count'] = $this->inpage;

        $this->totalpages = (int)ceil($total / $this->inpage);

        $this->res = $this->buildResult();
    }

    private function buildResult(): array
    {
        $half = (int)((self::LINK_COUNT - 1) / 2);
        $res  = [];

        $res['Page']  = $this->page + 1;
        $res['Count'] = [];

        if ($this->page <= $half) {
            $c = min($this->totalpages, self::LINK_COUNT);
            for ($i = 0; $i < $c; $i++) {
                $res['Count'][] = [$i + 1, $i];
            }
            if ($this->totalpages > self::LINK_COUNT) {
                $res['Count'][] = ['...', $c];
            }
        } else {
            $rangeEnd = min($this->totalpages - $this->page, $half);
            $c        = $half + $rangeEnd;

            if ($this->page > $half) {
                $res['Count'][] = ['...', $this->page - $half - 1];
            }
            for ($i = 0; $i < $c; $i++) {
                $res['Count'][] = [
                    $i + $this->page - $half + 1,
                    $i + $this->page - $half,
                ];
            }
            if ($this->totalpages > $this->page + $half) {
                $res['Count'][] = ['...', $this->page + $half + 1];
            }
        }

        // Р вЂ™Р В°РЎР‚Р С‘Р В°Р Р…РЎвЂљРЎвЂ№ Р’В«Р С—Р С• РЎРѓР С”Р С•Р В»РЎРЉР С”Р С• Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р ВµР’В»
        $res['InPage'] = range(self::PER_PAGE_START, self::PER_PAGE_END, self::PER_PAGE_STEP);

        $res['Start'] = $this->inpage * $this->page;
        $res['Limit'] = $this->inpage;

        return $res;
    }

    public function get(string $key): mixed
    {
        return $this->res[$key] ?? null;
    }

    /** @return array{Page: int, Count: array, InPage: array} */
    public function smartyArr(): array
    {
        return [
            'Page'   => $this->res['Page'],
            'Count'  => $this->res['Count'],
            'InPage' => $this->res['InPage'],
        ];
    }
}
