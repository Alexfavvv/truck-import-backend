<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PostSeeder extends Seeder
{
    /**
     * Seed initial blog posts into the database.
     */
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Прямые поставки оригинальных запчастей из Европы в 2026 году',
                'slug' => 'pryamye-postavki-zapchastej-iz-evropy-2026',
                'excerpt' => 'Компания Трак Импорт расширяет логистические маршруты для бесперебойных поставок оригинальных комплектующих DAF, MAN, Volvo и Mercedes-Benz.',
                'content' => 'Мы рады сообщить о расширении наших логистических сетей для регулярных оптовых поставок оригинальных запчастей для коммерческого транспорта. Благодаря прямой интеграции с европейскими дистрибьюторами, наши клиенты получают 100% подлинную продукцию с полной технической документацией и официальной гарантией завода-изготовителя.',
                'cover_image' => '/images/warehouse-1.jpg',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(2),
                'views_count' => 342,
                'meta_title' => 'Прямые поставки запчастей из Европы | Трак Импорт',
                'meta_description' => 'Прямые оптовые поставки запчастей для грузовых авто DAF, MAN, Volvo, Mercedes из Европы.',
            ],
            [
                'title' => 'Как выбрать качественные фильтры для седельных тягачей MAN и Scania',
                'slug' => 'kak-vybrat-filtry-dlya-tyagachej-man-scania',
                'excerpt' => 'Рекомендации экспертов по подбору топливных, масляных и воздушных фильтров Kolbenschmidt и Hengst для европейских грузовиков.',
                'content' => 'Правильный подбор и своевременная замена фильтров — залог долговечной работы дизельного двигателя грузового автомобиля. В этой статье мы подробно анализируем различия оригинальных фильтров Hengst и Kolbenschmidt и делимся рекомендациями по межсервисным интервалам.',
                'cover_image' => '/images/warehouse-2.jpg',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(5),
                'views_count' => 218,
                'meta_title' => 'Подбор фильтров MAN и Scania | Трак Импорт',
                'meta_description' => 'Как выбрать оригинальные масляные и топливные фильтры для коммерческих тягачей.',
            ],
            [
                'title' => 'Обслуживание тормозной системы грузовых автомобилей перед сезоном',
                'slug' => 'obsluzhivanie-tormoznoj-sistemy-gruzovikov',
                'excerpt' => 'Обзор износостойких тормозных дисков, суппортов и колодок для тягачей Volvo FH и Mercedes-Benz Actros.',
                'content' => 'Тормозная система тяжелой коммерческой техники испытывает максимальные нагрузки. В этой статье мы разбираем ключевые моменты диагностики и замены изношенных компонентов, а также преимущество использования оригинальных тормозных колодок и дисков.',
                'cover_image' => '/images/hero-truck.png',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(8),
                'views_count' => 456,
                'meta_title' => 'Обслуживание тормозов грузовиков | Трак Импорт',
                'meta_description' => 'Рекомендации по обслуживанию тормозной системы грузовиков Volvo и Mercedes.',
            ],
            [
                'title' => 'Складская программа Truck Import: более 4 000 000 деталей в наличии',
                'slug' => 'skladskaya-programma-truck-import-2026',
                'excerpt' => 'Масштабное обновление ассортимента запчастей для гидравлических систем, деталей двигателя и пневмоподвески.',
                'content' => 'Наши склады в регионах пополнились новыми партиями оригинальных деталей и надежных аналогов от ведущих мировых концернов. Быстрая отгрузка и собственная служба логистики гарантируют доставку точно в срок.',
                'cover_image' => '/images/laptop-dashboard.png',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(12),
                'views_count' => 512,
                'meta_title' => 'Складская программа 4 000 000 запчастей | Трак Импорт',
                'meta_description' => 'Огромный выбор автозапчастей в наличии на наших складах по всей России.',
            ],
            [
                'title' => 'Оптовые поставки запчастей для автопарков и сервисных центров',
                'slug' => 'optovye-postavki-zapchastej-dlya-avtoparkov',
                'excerpt' => 'Специальные оптовые цены, гибкая система отсрочки платежа и персональное сопровождение менеджером 24/7.',
                'content' => 'Трак Импорт предлагает особые условия сотрудничества для крупных транспортных компаний, сервисных станций (СТО) и региональных дистрибьюторов. Мы формируем индивидуальные прайс-листы и предоставляем отсрочку платежа.',
                'cover_image' => '/images/hero-manager.png',
                'is_published' => true,
                'published_at' => Carbon::now()->subDays(18),
                'views_count' => 389,
                'meta_title' => 'Запчасти оптом для СТО и автопарков | Трак Импорт',
                'meta_description' => 'Индивидуальные скидки и спецусловия для автопарков и грузовых сервисов.',
            ],
        ];

        foreach ($posts as $postData) {
            Post::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );
        }
    }
}
