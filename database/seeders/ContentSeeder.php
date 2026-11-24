<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Faq;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Initial website content taken from the Stitch design (adjusted to docs/decisions.md).
 * Everything here is editable in the admin. Texts marked as placeholders must be replaced
 * by the operator before launch (instructor, testimonials, prices).
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->courses();
        $this->team();
        $this->faqs();
        $this->testimonials();
        $this->pages();
    }

    private function courses(): void
    {
        $courses = [
            [
                'slug' => 'kpr-a-aed',
                'name' => 'KPR a záchrana života s AED',
                'badge' => 'Pokročilý tréning',
                'short_description' => 'Okamžitá reakcia pri náhlej zástave srdca a kolapse. Nácvik kvalitných kompresií hrudníka, bezpečné použitie defibrilátora AED, pomoc pri dusení a zotavovacia poloha.',
                'description' => '<p>Workshop zameraný na situácie, pri ktorých rozhodujú prvé minúty: náhla zástava srdca, bezvedomie a dusenie.</p><ul><li>rozpoznanie zástavy srdca a privolanie pomoci</li><li>kompresie hrudníka na figurínach so spätnou väzbou (hĺbka a frekvencia)</li><li>bezpečné použitie defibrilátora AED</li><li>dusenie u dospelých a detí (Heimlichov manéver)</li><li>zotavovacia poloha</li></ul>',
                'duration_label' => 'cca 3 hodiny',
                'equipment' => 'KPR figuríny, cvičný AED, resuscitačné rúška',
                'price' => 200,
                'image' => 'hero-cpr.webp',
                'is_featured' => true,
            ],
            [
                'slug' => 'prva-pomoc-doma-a-na-cestach',
                'name' => 'Civilná prvá pomoc doma a na cestách',
                'badge' => 'Univerzálny',
                'short_description' => 'Najčastejšie úrazy a akútne stavy: krvácanie a popáleniny, zlomeniny a fixácia končatín, anafylaktický šok, epileptický záchvat, otravy a špecifiká prvej pomoci u detí.',
                'description' => '<p>Komplexná prvá pomoc pre domácnosť, výlety aj cesty autom.</p><ul><li>zastavenie krvácania a ošetrenie popálenín</li><li>zlomeniny a fixácia končatín</li><li>alergická reakcia a anafylaktický šok</li><li>epileptický záchvat, mdloba, otravy</li><li>špecifiká prvej pomoci u malých detí</li></ul>',
                'duration_label' => 'cca 3,5 – 4 hodiny',
                'equipment' => 'Obväzový materiál, dlahy, trenažér dusenia',
                'price' => 200,
                'image' => 'workshop-family.webp',
                'is_featured' => false,
            ],
            [
                'slug' => 'takticka-prva-pomoc',
                'name' => 'Taktická prvá pomoc v kríze',
                'badge' => 'Špecializovaný',
                'short_description' => 'Život ohrozujúce úrazy a masívne krvácanie pri autonehodách, úrazoch v dielni či v prírode. Turnikety, tlakové obväzy a stabilizácia zraneného.',
                'description' => '<p>Workshop pre motoristov, kutilov, športovcov a všetkých, ktorí chcú zvládnuť aj vážne úrazy.</p><ul><li>masívne krvácanie a použitie turniketu (CAT, SOFTT)</li><li>tlakové a hemostatické obväzy</li><li>postup pri autonehode a zabezpečenie miesta</li><li>stabilizácia zraneného do príchodu záchranky</li></ul>',
                'duration_label' => 'cca 3 hodiny',
                'equipment' => 'Turnikety CAT, izraelské obväzy, hemostatiká',
                'price' => 200,
                'image' => 'workshop-tccc.webp',
                'is_featured' => false,
            ],
        ];

        foreach ($courses as $index => $data) {
            $image = $this->copyImage($data['image'], 'courses');

            Course::query()->updateOrCreate(['slug' => $data['slug']], [
                ...$data,
                'image' => $image,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function team(): void
    {
        TeamMember::query()->updateOrCreate(['name' => 'Boris Lacík'], [
            'role' => 'Profesionálny hasič a zdravotnícky záchranár',
            'quote' => 'V praxi vidím, že o prežití často nerozhodujú záchranári, ktorí prídu o niekoľko minút, ale ľudia, ktorí sú pri postihnutom v prvých minútach. Naučím vás jednoduché postupy tak, aby ste v kritickom okamihu konali bez váhania.',
            'bio' => "Pri výcviku spájam aktuálne odporúčania European Resuscitation Council s reálnou praxou zo zásahov.\nNa workshope vytvárame bezpečný priestor, kde si každú situáciu vyskúšate toľkokrát, kým sa nebudete cítiť pripravení.",
            'photo' => $this->copyImage('instructor.webp', 'team'),
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function faqs(): void
    {
        $faqs = [
            ['Ako prebieha workshop?', 'Termín si vyberiete v kalendári – doobeda (9:00 – 13:00) alebo poobede (15:00 – 19:00). Prídem k vám s predstihom a rozložím figuríny, cvičný defibrilátor AED aj obväzový materiál; stačí bežná obývačka alebo zasadačka. Potom prejdeme simulované situácie a každý si zásah vyskúša niekoľkokrát, kým sa z neho nestane prirodzený reflex. Na záver dostanete osvedčenie o absolvovaní a príručku do domácej lekárničky.'],
            ['Musíme mať doma špeciálny priestor?', 'Vôbec nie. Stačí bežná obývačka alebo zasadačka s voľným miestom na podlahe (približne 2 × 2 metre), kde položíme podložku a figuríny. Hygienické pomôcky a dezinfekciu prinesiem.'],
            ['Pre koho je workshop vhodný? Môžu sa zúčastniť aj deti?', 'Workshop je určený pre dospelých a staršie deti (odporúčame od 11 rokov, keď už majú dosť sily na účinné stláčanie hrudníka). Rodinný formát je obľúbený, pretože členovia domácnosti trénujú ako zohraný tím.'],
            ['Koľko ľudí sa môže zúčastniť?', 'Workshop je pre 2 až 4 osoby, ktoré sa poznajú – rodina, priatelia alebo kolegovia. Cena je za celú skupinu.'],
            ['Kam za vami prídete?', 'Pôsobím v Bratislave a okolí. Doprava do 60 km od Bratislavy je v cene, pri väčšej vzdialenosti sa dohodneme individuálne.'],
            ['Ako a kedy sa platí?', 'Platí sa až po skončení workshopu, v hotovosti alebo kartou.'],
            ['Dostaneme potvrdenie o absolvovaní?', 'Áno, každý účastník dostane osvedčenie o absolvovaní workshopu. Nejde o akreditovaný kurz (napríklad pre BOZP alebo vodičský preukaz).'],
        ];

        foreach ($faqs as $index => [$question, $answer]) {
            Faq::query()->updateOrCreate(['sort_order' => $index + 1], [
                'question' => $question,
                'answer' => $answer,
                'is_active' => true,
            ]);
        }
    }

    private function testimonials(): void
    {
        $testimonials = [
            ['Martina a Juraj K.', 'Máme doma ročné dieťa a stále sme mali strach z dusenia. Boris prišiel v sobotu poobede k nám, všetko nám ukázal a trénovali sme, kým sme si neboli istí. Obrovská úľava.', 'Workshop KPR a AED • Bratislava'],
            ['Tomáš V. s partiou', 'Taktický workshop bol výborný. Žiadna nuda, odpovedal na každú otázku a priškrtenie turniketom sme si vyskúšali aj pod tlakom. Odporúčam každému motoristovi aj kutilovi.', 'Taktická prvá pomoc • Senec'],
        ];

        foreach ($testimonials as $index => [$author, $text, $context]) {
            Testimonial::query()->updateOrCreate(['author' => $author], [
                'text' => $text,
                'context' => $context,
                'rating' => 5,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function pages(): void
    {
        $pages = [
            'home' => ['Prvá pomoc bez váhania – workshopy u vás doma', 'Praktické workshopy prvej pomoci pre 2 – 4 osoby priamo u vás doma alebo v kancelárii. KPR a AED, prvá pomoc pre rodiny, taktická prvá pomoc. Bratislava a okolie.'],
            'booking' => ['Rezervácia termínu workshopu', 'Vyberte si voľný termín workshopu prvej pomoci v kalendári – doobeda alebo poobede, u vás doma v Bratislave a okolí.'],
            'privacy' => ['Ochrana osobných údajov', 'Informácie o spracúvaní osobných údajov pri rezervácii workshopu a kontaktnom formulári.'],
            'terms' => ['Podmienky rezervácie', 'Podmienky rezervácie a zrušenia workshopov prvej pomoci Bez váhania.'],
            'cookies' => ['Zásady používania cookies', 'Aké cookies používame a ako môžete zmeniť svoj súhlas.'],
        ];

        foreach ($pages as $key => [$seoTitle, $seoDescription]) {
            $page = Page::query()->firstOrNew(['key' => $key]);
            $page->fill([
                'title' => __("site.pages.{$key}"),
                'seo_title' => $page->seo_title ?: $seoTitle,
                'seo_description' => $page->seo_description ?: $seoDescription,
            ])->save();
        }

        $cookies = Page::query()->where('key', 'cookies')->firstOrFail();
        if (! $cookies->content) {
            $cookies->update(['content' => '<h2>Čo sú cookies</h2><p>Cookies sú malé súbory, ktoré web ukladá vo vašom prehliadači.</p><h2>Nevyhnutné cookies</h2><p>Zabezpečujú fungovanie webu a ochranu formulárov (napr. <code>XSRF-TOKEN</code> a cookie relácie). Na ich použitie nepotrebujeme súhlas.</p><h2>Analytické cookies</h2><p>Google Analytics (cookies <code>_ga</code>, <code>_ga_*</code>) používame iba vtedy, ak s tým súhlasíte v cookie lište. Pomáhajú nám pochopiť, ako sa web používa.</p><h2>Zmena súhlasu</h2><p>Svoj súhlas môžete kedykoľvek zmeniť cez odkaz „Nastavenia cookies“ v pätičke webu.</p>']);
        }
    }

    /**
     * Copies a bundled design image to the public disk so it can be replaced from the admin.
     */
    private function copyImage(string $file, string $directory): string
    {
        $path = "{$directory}/{$file}";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, (string) file_get_contents(resource_path("images/{$file}")));
        }

        return $path;
    }
}
