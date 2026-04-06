<?php

namespace App\Console\Commands;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\KnowledgeBaseArticle;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedDemoCommand extends Command
{
    protected $signature = 'db:seed-demo {--fresh : Wipe existing demo data before seeding}';

    protected $description = 'Seed the database with realistic demo data that showcases LanHelp capabilities';

    /** @var array<string, User> */
    private array $users = [];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->components->warn('Wiping existing data...');
            TicketReply::query()->delete();
            Ticket::query()->delete();
            KnowledgeBaseArticle::query()->delete();
            User::query()->where('email', 'not like', '%@example.com')->delete();
        }

        $this->createUsers();
        $this->createKnowledgeBase();
        $this->createTickets();

        $this->newLine();
        $this->components->info('Demo data seeded successfully.');
        $this->newLine();

        $this->table(['Role', 'Email', 'Password'], [
            ['Admin', 'admin@lanhelp.test', 'password'],
            ['Staff', 'staff@lanhelp.test', 'password'],
            ['Staff', 'staff2@lanhelp.test', 'password'],
            ['User', 'alice@lanhelp.test', 'password'],
            ['User', 'bob@lanhelp.test', 'password'],
            ['User', 'charlie@lanhelp.test', 'password'],
        ]);

        return self::SUCCESS;
    }

    private function createUsers(): void
    {
        $this->components->task('Creating users', function () {
            $this->users['admin'] = User::factory()->admin()->create([
                'name' => 'Hannah Organizer',
                'email' => 'admin@lanhelp.test',
                'password' => Hash::make('password'),
            ]);

            $this->users['staff1'] = User::factory()->staff()->create([
                'name' => 'Felix Support',
                'email' => 'staff@lanhelp.test',
                'password' => Hash::make('password'),
            ]);

            $this->users['staff2'] = User::factory()->staff()->create([
                'name' => 'Mira Techsupport',
                'email' => 'staff2@lanhelp.test',
                'password' => Hash::make('password'),
            ]);

            $this->users['alice'] = User::factory()->create([
                'name' => 'Alice Gamer',
                'email' => 'alice@lanhelp.test',
                'password' => Hash::make('password'),
            ]);

            $this->users['bob'] = User::factory()->create([
                'name' => 'Bob Attendee',
                'email' => 'bob@lanhelp.test',
                'password' => Hash::make('password'),
            ]);

            $this->users['charlie'] = User::factory()->create([
                'name' => 'Charlie Newcomer',
                'email' => 'charlie@lanhelp.test',
                'password' => Hash::make('password'),
            ]);
        });
    }

    private function createKnowledgeBase(): void
    {
        $this->components->task('Creating knowledge base articles', function () {
            $admin = $this->users['admin'];

            $this->article($admin, 'Getting Started at the LAN Party', 'getting-started', 'general', <<<'MD'
            Welcome to the LAN party! Here's everything you need to know to get set up.

            ## What to bring
            - Your PC or laptop with power cable
            - An Ethernet cable (at least 5m recommended)
            - Headset or headphones
            - Your ticket (digital or printed)

            ## Check-in process
            Head to the entrance desk with your ticket. Staff will scan your QR code and assign you a seat. Your seat number will be displayed on the screen at the entrance.

            ## Network setup
            Plug in your Ethernet cable to the port at your desk. DHCP is enabled — your PC should get an IP address automatically. WiFi is available as a backup but wired connections are strongly recommended for gaming.

            ## Need help?
            Submit a ticket through LanHelp or visit the support desk near the entrance. Staff are available 24/7 during the event.
            MD);

            $this->article($admin, 'WiFi and Network Troubleshooting', 'wifi-network-troubleshooting', 'technical', <<<'MD'
            Having trouble connecting? Try these steps.

            ## Wired connection not working
            1. Check that your Ethernet cable is firmly plugged in at both ends
            2. Try a different port on the switch at your desk
            3. Open a command prompt and run `ipconfig /release` then `ipconfig /renew`
            4. If you still have no connection, submit a ticket with your seat number

            ## WiFi connection
            - Network name: **LanParty-Guest**
            - Password: **displayed at the entrance screen**
            - Note: WiFi is shared and not suitable for competitive gaming

            ## Slow speeds
            If your connection is slow, check that you're not running downloads or updates in the background. Steam, Windows Update, and game launchers can consume significant bandwidth. Please pause any large downloads during tournament matches.
            MD);

            $this->article($admin, 'Tournament Rules and Fair Play', 'tournament-rules', 'events', <<<'MD'
            All participants must follow these rules during tournaments.

            ## General rules
            - Be on time for your matches. A 10-minute grace period applies, after which you forfeit.
            - Use only approved peripherals. Macro mice and programmable keyboards are allowed unless the specific tournament rules say otherwise.
            - No cheating software of any kind. Any detection results in immediate disqualification and a ban from future events.

            ## Disputes
            If you disagree with a match result, contact a tournament admin immediately. Do not argue with other players. All admin decisions are final.

            ## Communication
            Keep team voice comms to your headset. No open speakers during tournament matches. Be respectful to opponents and staff at all times.
            MD);

            $this->article($admin, 'Food, Drinks and Sleeping', 'food-drinks-sleeping', 'general', <<<'MD'
            ## Food and drinks
            The venue has a catering area near the back with hot meals, snacks, and drinks available 24/7. You can also bring your own food, but please keep your desk area clean.

            **No alcohol** is permitted in the gaming area. Drinks with lids only at your desk — no open cups near equipment.

            ## Sleeping area
            A quiet sleeping area is available on the second floor with mats and pillows. Bring your own sleeping bag if you plan to stay overnight. Please keep noise to an absolute minimum in this area.

            ## Showers
            Showers are available in the changing rooms near the sleeping area. Towels are not provided.
            MD);

            $this->article($admin, 'How to Submit a Support Ticket', 'how-to-submit-ticket', 'account', <<<'MD'
            If you need help during the event, the fastest way to reach us is through a support ticket.

            ## Steps
            1. Log in to LanHelp with your LanCore account
            2. Click **"Submit a ticket"** from the landing page or navigate to **Tickets → New Ticket**
            3. Choose a priority level:
               - **Low**: General questions, non-urgent requests
               - **Normal**: Standard issues that need attention
               - **High**: Problems affecting your ability to participate
               - **Urgent**: Critical issues (network down for your entire row, safety concerns)
            4. Describe your issue in detail. Include your **seat number** if relevant.
            5. Submit and wait for a staff response. You'll be notified when staff replies.

            ## Response times
            - Urgent: within 15 minutes
            - High: within 30 minutes
            - Normal: within 2 hours
            - Low: within the event day
            MD);

            // One draft article
            KnowledgeBaseArticle::factory()->create([
                'author_id' => $admin->id,
                'title' => 'Prize Pool Distribution (Draft)',
                'slug' => 'prize-pool-distribution',
                'content' => "This article is still being finalized.\n\nPrize pool details will be announced before the tournament starts.",
                'category' => 'events',
                'is_published' => false,
                'published_at' => null,
            ]);
        });
    }

    private function createTickets(): void
    {
        $this->components->task('Creating tickets with conversations', function () {
            $admin = $this->users['admin'];
            $staff1 = $this->users['staff1'];
            $staff2 = $this->users['staff2'];
            $alice = $this->users['alice'];
            $bob = $this->users['bob'];
            $charlie = $this->users['charlie'];

            // 1. Resolved ticket with full conversation
            $t1 = $this->ticket($alice, 'No network connection at seat B-14', <<<'TXT'
            I plugged in my Ethernet cable but I'm not getting any IP address. The link light on my laptop is on so the cable seems fine. I tried two different ports on the switch. Running Windows 11.

            Seat: B-14
            TXT, TicketPriority::High, TicketStatus::Resolved, 'technical', $staff1);

            $this->reply($t1, $staff1, 'Hi Alice, thanks for the details. I can see that port B-14 is showing as disabled in our switch config. Let me re-enable it — give me 2 minutes.', hoursAgo: 3);
            $this->reply($t1, $staff1, "Port is back up. Can you try running `ipconfig /renew` now? You should get a 10.0.x.x address.", hoursAgo: 2, minutesAgo: 50);
            $this->reply($t1, $alice, "That worked! I'm connected now with 10.0.42.14. Thanks for the fast help!", hoursAgo: 2, minutesAgo: 40);
            $this->reply($t1, $staff1, "Great, glad it's working! Closing this ticket. Let us know if you have any other issues.", hoursAgo: 2, minutesAgo: 35, internal: false);
            $this->reply($t1, $staff1, 'Note: Port was disabled due to MAC flapping from previous event. Added to post-event checklist to reset all ports.', hoursAgo: 2, minutesAgo: 30, internal: true);

            // 2. In-progress urgent ticket
            $t2 = $this->ticket($bob, 'Entire row C has no power', <<<'TXT'
            The whole power strip for row C just died. About 8 people have lost power including me. Some PCs shut down abruptly. This is urgent — there's a tournament match in 30 minutes.

            Seat: C-03
            TXT, TicketPriority::Urgent, TicketStatus::InProgress, 'technical', $staff2);

            $this->reply($t2, $staff2, "I'm on my way to row C now. Please tell everyone in the row not to unplug anything — we need to check the breaker first.", hoursAgo: 1, minutesAgo: 15);
            $this->reply($t2, $staff2, "Found the issue — breaker tripped due to overload. We're redistributing the load across two circuits. ETA 10 minutes to restore power.", hoursAgo: 1, minutesAgo: 5);
            $this->reply($t2, $staff2, 'Notified tournament admins about the situation in row C. Matches for affected players will be delayed if needed.', hoursAgo: 1, internal: true);

            // 3. Open ticket, unassigned
            $this->ticket($charlie, 'Can I change my seat?', <<<'TXT'
            Hi, I'm at seat A-22 but my friend is at D-05. Is it possible to move to a seat near him? We'd like to be in the same row for the team tournament later.
            TXT, TicketPriority::Low, TicketStatus::Open, 'general');

            // 4. Waiting for user
            $t4 = $this->ticket($alice, 'Steam game won\'t launch — anti-cheat error', <<<'TXT'
            I'm trying to launch CS2 but I get an anti-cheat initialization error. I've verified game files and reinstalled the anti-cheat client. Same error every time.

            Error message: "VAC Authentication Error — Please restart your game"
            TXT, TicketPriority::Normal, TicketStatus::WaitingForUser, 'technical', $staff1);

            $this->reply($t4, $staff1, "This usually happens when there's a conflicting process running. Can you try these steps:\n\n1. Close Steam completely\n2. Open Task Manager and end any `vac` or `steamservice` processes\n3. Run Steam as administrator\n4. Try launching CS2 again\n\nLet me know if that helps.", hoursAgo: 4);

            // 5. Closed ticket
            $t5 = $this->ticket($bob, 'Where can I buy tournament tickets?', <<<'TXT'
            I want to sign up for the Rocket League 2v2 tournament but I can't find where to register. Is there a separate ticket I need to buy?
            TXT, TicketPriority::Low, TicketStatus::Closed, 'general', $staff1);

            $this->reply($t5, $staff1, "Tournament registration is handled through LanCore, not LanHelp. Go to the event page on LanCore and you'll see all available competitions listed there. Registration is free for all attendees!", hoursAgo: 8);
            $this->reply($t5, $bob, 'Found it, thank you!', hoursAgo: 7, minutesAgo: 45);

            // 6. High priority with context snapshot
            $t6 = $this->ticket($charlie, 'Monitor flickering after power outage', <<<'TXT'
            After the power came back on in my area, my monitor started flickering badly. It's a 144Hz display connected via DisplayPort. It was working fine before the outage.
            TXT, TicketPriority::High, TicketStatus::Open, 'technical');
            $t6->update(['context_snapshot' => [
                'source_product' => 'LanCore',
                'source_domain' => 'events',
                'event_reference' => 'EVT-2026',
                'seat_reference' => 'SEAT-A22',
                'links' => [
                    ['label' => 'Event Page', 'url' => 'http://localhost/events/lan-party-spring-2026'],
                ],
            ]]);

            // 7-10. Additional tickets for volume
            $this->ticket($alice, 'Request for extra Ethernet cable', 'My cable is too short to reach the switch. Can I get a longer one from the support desk? I need about 10 meters.', TicketPriority::Low, TicketStatus::Resolved, 'general', $staff2);

            $this->ticket($bob, 'Loud music from neighbor', "The person at seat D-12 is playing music through external speakers very loudly. I've asked them to stop but they won't. Can staff please intervene?", TicketPriority::Normal, TicketStatus::InProgress, 'general', $staff1);

            $t9 = $this->ticket($charlie, 'LanCore login not working', "I'm trying to log into LanCore to register for the tournament but I keep getting 'Invalid credentials'. I'm sure my password is correct — I just changed it yesterday.", TicketPriority::Normal, TicketStatus::Resolved, 'account', $staff2);
            $this->reply($t9, $staff2, "I've checked your account and it looks like there's a case sensitivity issue with your email. Your account is registered under Charlie.Newcomer@gmail.com (capital C and N). Try logging in with exactly that.", hoursAgo: 5);
            $this->reply($t9, $charlie, "That was it! I was using all lowercase. Maybe the login should be case-insensitive? Anyway, I'm in now. Thanks!", hoursAgo: 4, minutesAgo: 50);

            $this->ticket($alice, 'Catering feedback — more vegetarian options please', "The hot food selection is great but there's only one vegetarian option (pasta). Could we get more variety for the next meal rotation? A veggie burger or salad bowl would be awesome.", TicketPriority::Low, TicketStatus::Open, 'general');
        });
    }

    private function article(User $author, string $title, string $slug, string $category, string $content): KnowledgeBaseArticle
    {
        return KnowledgeBaseArticle::factory()->published()->create([
            'author_id' => $author->id,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => Str::limit(strip_tags($content), 150),
            'category' => $category,
        ]);
    }

    private function ticket(
        User $requester,
        string $subject,
        string $description,
        TicketPriority $priority,
        TicketStatus $status,
        ?string $category = null,
        ?User $assignee = null,
    ): Ticket {
        return Ticket::factory()->create([
            'requester_id' => $requester->id,
            'assignee_id' => $assignee?->id,
            'subject' => $subject,
            'description' => $description,
            'priority' => $priority,
            'status' => $status,
            'category' => $category,
            'resolved_at' => $status === TicketStatus::Resolved || $status === TicketStatus::Closed ? now()->subHours(2) : null,
            'closed_at' => $status === TicketStatus::Closed ? now()->subHour() : null,
            'created_at' => now()->subHours(fake()->numberBetween(1, 12)),
        ]);
    }

    private function reply(
        Ticket $ticket,
        User $author,
        string $body,
        int $hoursAgo = 0,
        int $minutesAgo = 0,
        bool $internal = false,
    ): TicketReply {
        return TicketReply::factory()->create([
            'ticket_id' => $ticket->id,
            'author_id' => $author->id,
            'body' => $body,
            'is_internal' => $internal,
            'created_at' => now()->subHours($hoursAgo)->subMinutes($minutesAgo),
        ]);
    }
}
