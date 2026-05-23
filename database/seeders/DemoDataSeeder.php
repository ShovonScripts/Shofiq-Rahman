<?php

namespace Database\Seeders;

use App\Models\AuthorAchievement;
use App\Models\AuthorEvent;
use App\Models\AuthorProfile;
use App\Models\Book;
use App\Models\BookLink;
use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Genre;
use App\Models\HomeBanner;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Shofiq Rahman',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $author = User::where('email', 'author@writer.bd')->first();
        if (!$author) {
            $author = User::create([
                'name' => 'লেখক',
                'email' => 'author@writer.bd',
                'password' => bcrypt('author123'),
                'role' => 'author',
                'is_active' => true,
            ]);
        }

        $reader = User::where('email', 'reader@writer.bd')->first();
        if (!$reader) {
            $reader = User::create([
                'name' => 'পাঠক',
                'email' => 'reader@writer.bd',
                'password' => bcrypt('reader123'),
                'role' => 'reader',
                'is_active' => true,
            ]);
        }

        $this->seedPosts($admin);
        $this->seedBooks($admin);
        $this->seedBanners();
        $this->seedContactMessages();
        $this->seedComments($admin, $reader);
        $this->seedLikes($reader);

        $this->command->info('Demo data seeded successfully!');
    }

    private function seedPosts(User $admin): void
    {
        $categories = Category::all();
        $genres = Genre::all();

        $posts = [
            [
                'title' => 'বাংলা সাহিত্যের ধারা',
                'slug' => 'bangla-sahittyer-dhara',
                'excerpt' => 'বাংলা সাহিত্যের বিভিন্ন ধারা ও তার বিবর্তন নিয়ে আলোচনা।',
                'body' => "## বাংলা সাহিত্যের ধারা\n\nবাংলা সাহিত্য একটি সমৃদ্ধ ঐতিহ্যের ধারক। প্রাচীন যুগ থেকে শুরু করে আধুনিক যুগ পর্যন্ত বাংলা সাহিত্য বিভিন্ন ধারায় বিভক্ত।\n\n### প্রাচীন যুগ\n\nচর্যাপদ বাংলা সাহিত্যের প্রাচীনতম নিদর্শন। এটি বৌদ্ধ ধর্মের গান ও পদ্যের সংকলন।\n\n### মধ্যযুগ\n\nমধ্যযুগে বাংলা সাহিত্যে বৈষ্ণব পদাবলী, মঙ্গলকাব্য এবং অনুবাদ সাহিত্যের বিকাশ ঘটে।\n\n### আধুনিক যুগ\n\nউনিশ শতক থেকে বাংলা সাহিত্যে আধুনিকতার ছোঁয়া লাগে। রবীন্দ্রনাথ ঠাকুর, কাজী নজরুল ইসলাম, শরৎচন্দ্র চট্টোপাধ্যায় প্রমুখ সাহিত্যিকরা বাংলা সাহিত্যকে সমৃদ্ধ করেছেন।\n\n#### সমকালীন সাহিত্য\n\nবর্তমানে বাংলা সাহিত্য বিশ্বসাহিত্যের মূলধারায় নিজের অবস্থান শক্তিশালী করছে। তরুণ লেখকরা নতুন নতুন বিষয় ও ফরম্যাট নিয়ে পরীক্ষা-নিরীক্ষা চালাচ্ছেন।",
                'reading_time' => 4,
                'status' => 'published',
                'published_at' => now()->subDays(30),
                'categories' => ['essay'],
                'genres' => ['contemporary', 'historical'],
            ],
            [
                'title' => 'প্রকাশনা ব্যবসার ভবিষ্যৎ',
                'slug' => 'prokashona-business-future',
                'excerpt' => 'ডিজিটাল যুগে প্রকাশনা শিল্প কীভাবে পরিবর্তিত হচ্ছে এবং এর ভবিষ্যৎ কী।',
                'body' => "## ডিজিটাল প্রকাশনা: নতুন সম্ভাবনা\n\nপ্রযুক্তির উন্নতির সাথে সাথে প্রকাশনা শিল্পেও এসেছে অভূতপূর্ব পরিবর্তন। ডিজিটাল প্রকাশনা এখন আর ভবিষ্যতের বিষয় নয়, বর্তমানের বাস্তবতা।\n\n### ই-বুক ও অডিওবুক\n\nপাঠকের অভ্যাস বদলাচ্ছে। ই-বুক ও অডিওবুকের জনপ্রিয়তা দিন দিন বাড়ছে। বিশেষ করে তরুণ প্রজন্মের মধ্যে ডিজিটাল পড়ার প্রবণতা লক্ষণীয়।\n\n### সেলফ-পাবলিশিং\n\nলেখকরা এখন নিজেরাই নিজেদের বই প্রকাশ করতে পারছেন। Amazon Kindle Direct Publishing, Rokomari-এর সেলফ-পাবলিশিং প্ল্যাটফর্ম - এগুলো লেখকদের নতুন সুযোগ করে দিচ্ছে।\n\n### চ্যালেঞ্জ\n\n- কপিরাইট সুরক্ষা\n- মান নিয়ন্ত্রণ\n- পাঠকের মনোযোগ ধরে রাখা\n- ডিজিটাল ও প্রিন্টের মধ্যে সমন্বয়\n\n### ভবিষ্যৎ\n\nহাইব্রিড মডেলই ভবিষ্যৎ। প্রিন্ট ও ডিজিটাল - দুটোরই নিজস্ব জায়গা থাকবে। Augmented Reality (AR) এবং Artificial Intelligence (AI) প্রকাশনা শিল্পে নতুন মাত্রা যোগ করবে।",
                'reading_time' => 5,
                'status' => 'published',
                'published_at' => now()->subDays(20),
                'categories' => ['essay'],
                'genres' => ['contemporary', 'inspirational'],
            ],
            [
                'title' => 'শিক্ষার আলো',
                'slug' => 'shikkhar-alo',
                'excerpt' => 'শিক্ষার গুরুত্ব ও সমাজে তার প্রভাব নিয়ে একটি রচনা।',
                'body' => "## শিক্ষা: মানবজীবনের আলো\n\nশিক্ষা মানুষের জীবনকে আলোকিত করে। এটি শুধু বই পড়া বা পরীক্ষায় পাশ করার নাম নয়, বরং জীবনকে দেখা ও বোঝার একটি উপায়।\n\n### শিক্ষার উদ্দেশ্য\n\nশিক্ষার মূল উদ্দেশ্য হলো মানুষকে সঠিক ও ভুলের মধ্যে পার্থক্য করতে শেখানো, তাকে একটি সুনাগরিক হিসেবে গড়ে তোলা এবং তার সুপ্ত প্রতিভার বিকাশ ঘটানো।\n\n### শিক্ষা ও সমাজ\n\nশিক্ষিত সমাজই উন্নত সমাজ। শিক্ষা মানুষের দৃষ্টিভঙ্গি পরিবর্তন করে, তাকে উদার ও সহনশীল করে তোলে। একটি জাতির উন্নতি নির্ভর করে তার শিক্ষার মানের উপর।\n\n### বর্তমান চ্যালেঞ্জ\n\n- শিক্ষার অপ্রতুলতা\n- গুণগত মানের অভাব\n- প্রযুক্তির সঠিক ব্যবহার\n- দক্ষ শিক্ষকের অভাব",
                'reading_time' => 3,
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'categories' => ['essay'],
                'genres' => ['inspirational', 'contemporary'],
            ],
            [
                'title' => 'একটি বিকেলের গল্প',
                'slug' => 'ekti-bikeler-golpo',
                'excerpt' => 'একটি সাধারণ বিকেলে ঘটে যাওয়া অসাধারণ একটি ঘটনার বিবরণ।',
                'body' => "## একটি বিকেলের গল্প\n\nবিকেলের রোদ জানালা দিয়ে ঘরে ঢুকছে। আমি বারান্দায় বসে চা খাচ্ছি। হঠাৎ দরজায় কে একজন নক করল।\n\nদরজা খুলে দেখি এক অচেনা মানুষ। বয়স大概 পঞ্চাশের কাছাকাছি। হাতে একটি পুরনো ব্রিফকেস।\n\n— আপনি কি রহমান সাহেব?\n\n— হ্যাঁ, বলুন।\n\n— আমি আপনার বাবার পুরনো বন্ধু। অনেক বছর পর এলাম।\n\nআমি অবাক। বাবা তো দশ বছর আগে মারা গেছেন। তাঁর বন্ধুদের অধিকাংশই এখন আর বেঁচে নেই।\n\n— ভিতরে আসুন, বললাম।\n\nবসার পর তিনি ব্রিফকেস খুলে একটি চিঠি বের করলেন। সেটি আমার বাবার লেখা।\n\nচিঠি পড়তে পড়তে চোখে পানি চলে এল। বাবার হাতের লেখা দেখে অনেক দিন পর মনটা ভরে গেল।\n\nসেই বিকেলে শুধু একটি চিঠিই পাইনি, বাবার সাথে কাটানো অনেক স্মৃতি ফিরে পেয়েছি।",
                'reading_time' => 3,
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'categories' => ['short-story'],
                'genres' => ['contemporary', 'social'],
            ],
            [
                'title' => 'স্বাধীনতা: এক রক্তক্ষয়ী ইতিহাস',
                'slug' => 'shadhinata-ektro-rokto-khoyi-itihash',
                'excerpt' => 'বাংলাদেশের স্বাধীনতা সংগ্রামের কিছু অজানা অধ্যায়।',
                'body' => "## স্বাধীনতা: এক রক্তক্ষয়ী ইতিহাস\n\n১৯৭১ সালের মহান মুক্তিযুদ্ধ বাঙালি জাতির জীবনের সবচেয়ে গৌরবময় অধ্যায়। নয় মাসের রক্তক্ষয়ী সংগ্রামের মাধ্যমে আমরা পেয়েছি আমাদের স্বাধীন বাংলাদেশ।\n\n### ভাষা আন্দোলন\n\n১৯৫২ সালের ভাষা আন্দোলন আমাদের স্বাধীনতার পথে প্রথম পদক্ষেপ। একুশে ফেব্রুয়ারি রাষ্ট্রভাষা বাংলার দাবিতে প্রাণ দিয়েছিলেন সালাম, রফিক, বরকত, জব্বার।\n\n### ছয় দফা\n\n১৯৬৬ সালে বঙ্গবন্ধু শেখ মুজিবুর রহমানের ছয় দফা ছিল স্বাধীনতার রোডম্যাপ। এটি বাঙালির মুক্তির সনদ হিসেবে পরিচিত।\n\n### ১৯৭১\n\n২৫ মার্চের কালো রাতে পাকিস্তানি হানাদার বাহিনী নিরস্ত্র বাঙালির উপর ঝাঁপিয়ে পড়ে। শুরু হয় মুক্তিযুদ্ধ। নয় মাসের রক্তক্ষয়ী যুদ্ধে আমাদের বিজয় এসেছে ১৬ ডিসেম্বর।\n\n### শ্রদ্ধা\n\nলক্ষ লক্ষ শহীদের আত্মত্যাগের বিনিময়ে পাওয়া এই স্বাধীনতা আমাদের কাছে চিরকাল অমূল্য।",
                'reading_time' => 4,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'categories' => ['essay', 'liberation-war'],
                'genres' => ['historical', 'inspirational'],
            ],
            [
                'title' => 'প্রযুক্তির অগ্রযাত্রা',
                'slug' => 'projuktir-ogrojatra',
                'excerpt' => 'কৃত্রিম বুদ্ধিমত্তা থেকে শুরু করে ব্লকচেইন - প্রযুক্তির বর্তমান অবস্থা ও ভবিষ্যৎ।',
                'body' => "## প্রযুক্তির অগ্রযাত্রা\n\nএকবিংশ শতাব্দীতে প্রযুক্তির অগ্রযাত্রা অভূতপূর্ব। প্রতিদিনই নতুন নতুন উদ্ভাবন আমাদের জীবনযাত্রাকে বদলে দিচ্ছে।\n\n### কৃত্রিম বুদ্ধিমত্তা\n\nArtificial Intelligence (AI) এখন আর কল্পবিজ্ঞানের বিষয় নয়। ChatGPT, Midjourney, Copilot - এগুলো বাস্তব। AI আমাদের কাজ করার পদ্ধতি বদলে দিচ্ছে।\n\n### ব্লকচেইন\n\nব্লকচেইন প্রযুক্তি শুধু ক্রিপ্টোকারেন্সির জন্যই নয়। এটি ডেটা নিরাপত্তা, সাপ্লাই চেইন ম্যানেজমেন্ট এবং ডিজিটাল আইডেন্টিটির ক্ষেত্রে বিপ্লব ঘটাতে পারে।\n\n### ইন্টারনেট অফ থিংস\n\nIoT ডিভাইস আমাদের ঘরবাড়ি, শহর ও শিল্পকে স্মার্ট করে তুলছে। স্মার্ট হোম, স্মার্ট সিটি - এগুলো এখন বাস্তবতা।\n\n### নৈতিকতা\n\nপ্রযুক্তির এই অগ্রযাত্রার সাথে সাথে নৈতিক প্রশ্নও সামনে আসছে। ডেটা প্রাইভেসি, AI-এর নৈতিক ব্যবহার, বায়াস - এসব বিষয়ে আমাদের সচেতন হতে হবে।",
                'reading_time' => 4,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'categories' => ['essay'],
                'genres' => ['contemporary', 'science-fiction'],
            ],
        ];

        foreach ($posts as $data) {
            $catSlugs = $data['categories'];
            $genSlugs = $data['genres'];
            unset($data['categories'], $data['genres']);

            $data['user_id'] = $admin->id;

            $post = Post::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            $post->categories()->syncWithoutDetaching(
                Category::whereIn('slug', $catSlugs)->pluck('id')
            );
            $post->genres()->syncWithoutDetaching(
                Genre::whereIn('slug', $genSlugs)->pluck('id')
            );

            $this->command->info("  Post: {$post->title}");
        }
    }

    private function seedBooks(User $admin): void
    {
        $genres = Genre::all();
        $categories = Category::all();

        $books = [
            [
                'title' => 'সময়ের ঢেউ',
                'slug' => 'somoyer-dheu',
                'subtitle' => 'একটি জীবনমুখী উপন্যাস',
                'description' => "## সময়ের ঢেউ\n\nএকটি মধ্যবিত্ত পরিবারের তিন প্রজন্মের গল্প নিয়ে 'সময়ের ঢেউ'। সময়ের সাথে সাথে কীভাবে সম্পর্ক, মূল্যবোধ এবং জীবনদর্শনের পরিবর্তন ঘটে, তা ফুটে উঠেছে এই উপন্যাসে।\n\n### প্রধান চরিত্র\n\n- **জামাল**: দাদা, যিনি গ্রাম থেকে শহরে এসেছিলেন\n- **কামাল**: বাবা, যিনি শহরেই বড় হয়েছেন\n- **শামীম**: ছেলে, যে বিদেশে পড়াশোনা করে দেশে ফিরেছে\n\nতিন প্রজন্মের দৃষ্টিভঙ্গির পার্থক্য ও মেলবন্ধনের গল্প। সময়ের ঢেউ যেমন একে একে এসে আছড়ে পড়ে, তেমনি জীবনের প্রতিটি অধ্যায় নিয়ে আসে নতুন চ্যালেঞ্জ ও সম্ভাবনা।",
                'status' => 'published',
                'published_at' => now()->subDays(60),
                'isbn' => '978-984-123-456-7',
                'language' => 'bn',
                'pages' => 256,
                'categories' => ['upanyas'],
                'genres' => ['contemporary', 'social'],
                'links' => [
                    ['label' => 'রকমারি', 'url' => 'https://rokomari.com/book/somoyer-dheu', 'is_primary' => true, 'sort_order' => 1],
                    ['label' => 'Amazon', 'url' => 'https://amazon.com/dp/example', 'is_primary' => false, 'sort_order' => 2],
                ],
            ],
            [
                'title' => 'গল্পের ঢেউ',
                'slug' => 'golper-dheu',
                'subtitle' => 'ছোটগল্প সংকলন',
                'description' => "## গল্পের ঢেউ\n\nছোটগল্পের এই সংকলনে রয়েছে দশটি ভিন্ন ভিন্ন স্বাদের গল্প। প্রেম, বিরহ, হাসি, কান্না - জীবনের সমস্ত রং ছড়িয়ে আছে এই গল্পগুলোতে।\n\n### গল্পসমূহ\n\n1. শেষ বিকেলের আলো\n2. একটি চিঠির গল্প\n3. নীল বসন্ত\n4. শহরের মানুষ\n5. গ্রামের পথে\n\nপ্রতিটি গল্পই পাঠককে অন্য এক জগতে নিয়ে যাবে। সহজ সরল ভাষায় লেখা গল্পগুলো হৃদয় স্পর্শ করবে।",
                'status' => 'published',
                'published_at' => now()->subDays(45),
                'isbn' => '978-984-234-567-8',
                'language' => 'bn',
                'pages' => 192,
                'categories' => ['short-story'],
                'genres' => ['contemporary', 'humor'],
                'links' => [
                    ['label' => 'রকমারি', 'url' => 'https://rokomari.com/book/golper-dheu', 'is_primary' => true, 'sort_order' => 1],
                ],
            ],
            [
                'title' => 'চিন্তার ঢেউ',
                'slug' => 'chintar-dheu',
                'subtitle' => 'প্রবন্ধ সংকলন',
                'description' => "## চিন্তার ঢেউ\n\nবাংলাদেশের সামাজিক, রাজনৈতিক ও সাংস্কৃতিক প্রেক্ষাপটে রচিত প্রবন্ধের সংকলন 'চিন্তার ঢেউ'। প্রতিটি প্রবন্ধই চিন্তার খোরাক জোগাবে।\n\n### আলোচিত বিষয়\n\n- শিক্ষার বর্তমান অবস্থা\n- সংস্কৃতির রূপান্তর\n- ভাষার ভবিষ্যৎ\n- প্রযুক্তির প্রভাব\n- সামাজিক মূল্যবোধ\n\nলেখকের তীক্ষ্ণ পর্যবেক্ষণ ও সহজ বিশ্লেষণ পাঠককে নতুন করে ভাবতে শেখাবে।",
                'status' => 'published',
                'published_at' => now()->subDays(30),
                'isbn' => '978-984-345-678-9',
                'language' => 'bn',
                'pages' => 224,
                'categories' => ['essay'],
                'genres' => ['contemporary', 'inspirational'],
                'links' => [
                    ['label' => 'রকমারি', 'url' => 'https://rokomari.com/book/chintar-dheu', 'is_primary' => true, 'sort_order' => 1],
                    ['label' => 'Amazon', 'url' => 'https://amazon.com/dp/example2', 'is_primary' => false, 'sort_order' => 2],
                ],
            ],
            [
                'title' => 'প্রেমের কবিতা',
                'slug' => 'premer-kobita',
                'subtitle' => 'কাব্য সংকলন',
                'description' => "## প্রেমের কবিতা\n\nপ্রেমের বিভিন্ন অনুভূতি নিয়ে লেখা কবিতার এই সংকলন। প্রথম প্রেম থেকে শুরু করে বিচ্ছেদের বেদনা, সব মিলিয়ে এক অপূর্ব কাব্যভুবন।",
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'isbn' => '978-984-456-789-0',
                'language' => 'bn',
                'pages' => 128,
                'categories' => ['kobita'],
                'genres' => ['romantic', 'contemporary'],
                'links' => [
                    ['label' => 'রকমারি', 'url' => 'https://rokomari.com/book/premer-kobita', 'is_primary' => true, 'sort_order' => 1],
                ],
            ],
        ];

        foreach ($books as $data) {
            $links = $data['links'];
            $catSlugs = $data['categories'];
            $genSlugs = $data['genres'];
            unset($data['links'], $data['categories'], $data['genres']);

            $data['user_id'] = $admin->id;

            $book = Book::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            $book->categories()->syncWithoutDetaching(
                Category::whereIn('slug', $catSlugs)->pluck('id')
            );
            $book->genres()->syncWithoutDetaching(
                Genre::whereIn('slug', $genSlugs)->pluck('id')
            );

            foreach ($links as $link) {
                BookLink::firstOrCreate(
                    ['book_id' => $book->id, 'url' => $link['url']],
                    $link + ['book_id' => $book->id]
                );
            }

            $this->command->info("  Book: {$book->title}");
        }
    }

    private function seedBanners(): void
    {
        $banners = [
            [
                'image' => 'https://picsum.photos/seed/banner1/1600/600',
                'title' => 'নতুন বই প্রকাশিত!',
                'link' => '/books/somoyer-dheu',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'image' => 'https://picsum.photos/seed/banner2/1600/600',
                'title' => 'সাম্প্রতিক ব্লগ পোস্ট',
                'link' => '/blog',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'image' => 'https://picsum.photos/seed/banner3/1600/600',
                'title' => 'লেখকের সাথে পরিচিত হোন',
                'link' => '/about',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $data) {
            HomeBanner::firstOrCreate(
                ['image' => $data['image'], 'title' => $data['title']],
                $data
            );
            $this->command->info("  Banner: {$data['title']}");
        }
    }

    private function seedContactMessages(): void
    {
        $messages = [
            [
                'name' => 'Rafiq Hasan',
                'email' => 'rafiq@example.com',
                'phone' => '+8801712345678',
                'subject' => 'বই নিয়ে প্রশ্ন',
                'message' => 'আপনার "সময়ের ঢেউ" বইটি সম্পর্কে আরও জানতে চাই। বইটির দাম কত এবং কোথায় কিনতে পারব?',
                'status' => 'unread',
                'ip_address' => '192.168.1.1',
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Nusrat Jahan',
                'email' => 'nusrat@example.com',
                'phone' => '+8801712345679',
                'subject' => 'ব্লগ পোস্ট নিয়ে মতামত',
                'message' => 'আপনার "প্রকাশনা ব্যবসার ভবিষ্যৎ" পোস্টটি খুবই চমৎকার হয়েছে। এই বিষয়ে আরও পোস্ট আশা করছি।',
                'status' => 'read',
                'ip_address' => '192.168.1.2',
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Tanvir Ahmed',
                'email' => 'tanvir@example.com',
                'subject' => 'সহযোগিতা প্রস্তাব',
                'message' => 'আমি একটি প্রকাশনা সংস্থার প্রতিনিধি। আপনার বই আমাদের মাধ্যমে প্রকাশের আগ্রহ আছে কিনা জানতে চাই।',
                'status' => 'replied',
                'ip_address' => '192.168.1.3',
                'replied_at' => now()->subDay(),
                'reply_subject' => 'Re: সহযোগিতা প্রস্তাব',
                'reply_body' => 'প্রিয় Tanvir, আপনার প্রস্তাবের জন্য ধন্যবাদ। আমরা অবশ্যই এই বিষয়ে আলোচনা করতে আগ্রহী। অনুগ্রহ করে আপনার প্রস্তাবের বিস্তারিত জানান।',
                'created_at' => now()->subDays(7),
            ],
        ];

        foreach ($messages as $data) {
            ContactMessage::firstOrCreate(
                ['email' => $data['email'], 'message' => $data['message']],
                $data
            );
            $this->command->info("  Message from: {$data['name']}");
        }
    }

    private function seedComments(User $admin, User $reader): void
    {
        $posts = Post::published()->get();
        $books = Book::published()->get();

        $comments = [
            [
                'user_id' => $reader->id,
                'body' => 'চমৎকার লেখা! বাংলা সাহিত্যের ধারা সম্পর্কে এত সুন্দরভাবে ব্যাখ্যা করার জন্য ধন্যবাদ।',
                'is_approved' => true,
            ],
            [
                'user_id' => $admin->id,
                'body' => 'আপনার মন্তব্যের জন্য ধন্যবাদ। আরও ভালো লেখা নিয়ে শীঘ্রই আসছি।',
                'is_approved' => true,
            ],
            [
                'user_id' => $reader->id,
                'body' => 'বইটি সম্পর্কে অনেক ভালো রিভিউ শুনেছি। আশা করি পড়ার সুযোগ হবে।',
                'is_approved' => true,
            ],
        ];

        foreach ($posts as $post) {
            foreach ($comments as $comment) {
                Comment::firstOrCreate(
                    [
                        'commentable_id' => $post->id,
                        'commentable_type' => Post::class,
                        'user_id' => $comment['user_id'],
                        'body' => $comment['body'],
                    ],
                    $comment + [
                        'commentable_id' => $post->id,
                        'commentable_type' => Post::class,
                        'created_at' => now()->subDays(rand(1, 14)),
                    ]
                );
            }
            $this->command->info("  Comments on post: {$post->title}");
        }

        foreach ($books as $book) {
            Comment::firstOrCreate(
                [
                    'commentable_id' => $book->id,
                    'commentable_type' => Book::class,
                    'user_id' => $reader->id,
                    'body' => 'বইটির বিবরণ পড়ে খুবই আগ্রহী হলাম। শীঘ্রই সংগ্রহ করব।',
                ],
                [
                    'user_id' => $reader->id,
                    'body' => 'বইটির বিবরণ পড়ে খুবই আগ্রহী হলাম। শীঘ্রই সংগ্রহ করব।',
                    'is_approved' => true,
                    'commentable_id' => $book->id,
                    'commentable_type' => Book::class,
                    'created_at' => now()->subDays(rand(1, 7)),
                ]
            );
            $this->command->info("  Comments on book: {$book->title}");
        }
    }

    private function seedLikes(User $reader): void
    {
        $posts = Post::published()->get();
        $books = Book::published()->get();

        foreach ($posts as $i => $post) {
            if ($i % 2 === 0) {
                Like::firstOrCreate([
                    'user_id' => $reader->id,
                    'likeable_id' => $post->id,
                    'likeable_type' => Post::class,
                ]);
            }
        }

        foreach ($books as $i => $book) {
            if ($i % 2 === 0) {
                Like::firstOrCreate([
                    'user_id' => $reader->id,
                    'likeable_id' => $book->id,
                    'likeable_type' => Book::class,
                ]);
            }
        }

        $this->command->info('  Likes seeded');
    }
}
