<?php

declare(strict_types=1);

namespace VivaArcade;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use VivaArcade\Economy\WalletService;
use VivaArcade\Challenges\DailyRewardService;
use VivaArcade\Forms\ArcadeForm;
use VivaArcade\Lobby\LobbyManager;
use VivaArcade\Party\PartyManager;
use VivaArcade\Games\ParkourRush;
use VivaArcade\Storage\JsonPlayerStorage;
use VivaArcade\World\ArcadeWorldManager;
use Throwable;

final class Main extends PluginBase implements Listener{

    private ?World $arcadeWorld = null;
    private ?LobbyManager $lobby = null;
    private ?JsonPlayerStorage $storage = null;
    private ?WalletService $wallet = null;
    private ?DailyRewardService $dailyRewards = null;
    private ?ParkourRush $parkour = null;
    private ?PartyManager $partyManager = null;
    private bool $worldPlayable = false;

    /** @var array<string, float> */
    private array $lastFormAt = [];

    protected function onEnable() : void{
        try{
            $this->saveDefaultConfig();
            $this->storage = new JsonPlayerStorage($this->getDataFolder() . 'players');
            $this->wallet = new WalletService($this->storage);
            $dailyConfig = $this->getConfig()->get('daily-reward', []);
            if(!is_array($dailyConfig)){
                $dailyConfig = [];
            }
            $this->dailyRewards = new DailyRewardService(
                $this->storage,
                $this->wallet,
                max(1, min(100_000, (int) ($dailyConfig['reward-dollar'] ?? 100))),
                max(0, min(100_000, (int) ($dailyConfig['reward-xp'] ?? 25)))
            );
            $partyConfig = $this->getConfig()->get('party', []);
            if(!is_array($partyConfig)){ $partyConfig = []; }
            $this->partyManager = new PartyManager(
                max(2, min(32, (int) ($partyConfig['max-members'] ?? 8))),
                max(5, min(3600, (int) ($partyConfig['invite-ttl-seconds'] ?? 60)))
            );
            $worldManager = new ArcadeWorldManager($this);
            $this->arcadeWorld = $worldManager->load(function() : void{
                $this->worldPlayable = true;
                foreach($this->getServer()->getOnlinePlayers() as $player){
                    $this->lobby?->teleport($player);
                }
            });
            $this->worldPlayable = $worldManager->isPlayable();

            $parkourConfig = $this->getConfig()->get('parkour', []);
            if(!is_array($parkourConfig)){
                $parkourConfig = [];
            }
            $this->parkour = new ParkourRush(
                $this->arcadeWorld,
                $this->storage,
                max(1, min(1000, (int) ($parkourConfig['reward-dollar'] ?? 50))),
                max(1, min(500, (int) ($parkourConfig['reward-xp'] ?? 30)))
            );
            $this->lobby = new LobbyManager($this->arcadeWorld);

            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            $status = $this->worldPlayable
                ? 'World layout ready.'
                : 'World layout is building or requires a reviewed migration; games remain disabled.';
            $this->getLogger()->info('VivaArcade enabled. ' . $status);
        }catch(Throwable $e){
            $this->getLogger()->critical('VivaArcade could not initialize its dedicated world.');
            $this->getLogger()->logException($e);
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    protected function onDisable() : void{
        if($this->parkour !== null && $this->arcadeWorld !== null){
            foreach($this->getServer()->getOnlinePlayers() as $player){
                $this->parkour->leave($player, true);
            }
        }
        $this->lastFormAt = [];
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if(!$sender instanceof Player){
            $sender->sendMessage('In dastoor faghat dar dakhele bazi ghabel ejra ast.');
            return true;
        }
        if(!$sender->hasPermission('vivaarcade.command')){
            $sender->sendMessage('Shoma ejaze estefade az VivaArcade ra nadarid.');
            return true;
        }

        switch(strtolower($command->getName())){
            case 'arcade':
                $this->openMainMenu($sender);
                return true;
            case 'games':
                $this->openGamesMenu($sender);
                return true;
            case 'profile':
                $this->showProfile($sender);
                return true;
            case 'stats':
                $this->showStatistics($sender);
                return true;
            case 'balance':
                $this->showBalance($sender);
                return true;
            case 'daily':
                $this->openDailyMenu($sender);
                return true;
            case 'party':
                $this->handlePartyCommand($sender, $args);
                return true;
            case 'spawn':
                $this->parkour?->leave($sender, false);
                if($this->lobby?->teleport($sender) !== true){
                    $sender->sendMessage('Bazgasht be Lobby anjam nashod.');
                }else{
                    $sender->sendMessage('Be Lobby bargashtid!');
                }
                return true;
            case 'leave':
                if($this->parkour?->leave($sender, true) !== true){
                    $sender->sendMessage('Shoma dar hich bazi faali nistid.');
                }
                return true;
        }
        return false;
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $this->lobby?->teleport($player);
        $player->sendMessage('Be VivaArcade khosh amadid! Baraye didane menu /arcade ra bezanid.');
    }

    public function onMove(PlayerMoveEvent $event) : void{
        $this->parkour?->handleMove($event);
    }

    public function onQuit(PlayerQuitEvent $event) : void{
        $uuid = $event->getPlayer()->getUniqueId()->toString();
        $this->parkour?->forget($uuid);
        $this->handlePartyDisconnect($uuid);
        unset($this->lastFormAt[$uuid]);
    }

    public function onBlockBreak(BlockBreakEvent $event) : void{
        if($this->arcadeWorld !== null && $event->getPlayer()->getWorld() === $this->arcadeWorld){
            $event->cancel();
            $event->getPlayer()->sendActionBarMessage('Sakht-o-saz dar donyaye VivaArcade ghayre faal ast.');
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) : void{
        if($this->arcadeWorld !== null && $event->getPlayer()->getWorld() === $this->arcadeWorld){
            $event->cancel();
            $event->getPlayer()->sendActionBarMessage('Sakht-o-saz dar donyaye VivaArcade ghayre faal ast.');
        }
    }

    private function openMainMenu(Player $player) : void{
        $this->sendForm($player, new ArcadeForm(
            'VivaArcade',
            'Be VivaArcade khosh amadid!\nBebin. Bazi kon. Challenge bede. Be bord beres!',
            ['Bazi', 'Party', 'Profile', 'Amar', 'Padesh Roozane', 'Komak', 'Bastan'],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    $this->openGamesMenu($target);
                }elseif($choice === 1){
                    $this->openPartyMenu($target);
                }elseif($choice === 2){
                    $this->showProfile($target);
                }elseif($choice === 3){
                    $this->showStatistics($target);
                }elseif($choice === 4){
                    $this->openDailyMenu($target);
                }elseif($choice === 5){
                    $this->openHelpMenu($target);
                }
            }
        ));
    }

    private function openGamesMenu(Player $player) : void{
        if(!$this->worldPlayable){
            $player->sendMessage('World dar hale sakht ast ya niaz be migration darad. Bazi pas az takmil faal mishavad.');
            return;
        }
        $this->sendForm($player, new ArcadeForm(
            'Entekhab Bazi',
            'Mode mored nazar ra baraye bazi entekhab kon.',
            ['Parkour Rush', 'Bargasht', 'Bastan'],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    if(!$this->parkour?->start($target)){
                        $target->sendMessage('Shoma alan dar yek bazi hastid.');
                    }
                }elseif($choice === 1){
                    $this->openMainMenu($target);
                }
            }
        ));
    }

    private function openDailyMenu(Player $player) : void{
        if($this->dailyRewards === null){
            $player->sendMessage('Padesh roozane dar hale hazer dar dastres nist.');
            return;
        }
        try{
            $uuid = $player->getUniqueId()->toString();
            $status = $this->dailyRewards->getStatus($uuid, $player->getName());
            $state = $status['claimed'] ? 'Daryaft Shode' : 'Amade Daryaft';
            $content = 'Padesh Roozane VivaArcade'
                . "\nTarikh: " . $status['date'] . ' (UTC)'
                . "\nPadesh: $" . $status['dollar'] . ' Dollar'
                . "\nXP: " . $status['xp']
                . "\nVaziyat: " . $state
                . "\nReset har rooz saat 00:00 UTC ast.";
            $this->sendForm($player, new ArcadeForm(
                'Padesh Roozane',
                $content,
                [$status['claimed'] ? 'Padesh Daryaft Shode' : 'Daryaft Padesh', 'Bargasht', 'Bastan'],
                function(Player $target, int $choice) use ($status) : void{
                    if($choice === 0){
                        if($status['claimed']){
                            $target->sendMessage('Padesh emrooz ghablan daryaft shode ast.');
                            return;
                        }
                        try{
                            $claimed = $this->dailyRewards?->claim(
                                $target->getUniqueId()->toString(),
                                $target->getName()
                            ) ?? false;
                            if($claimed){
                                $target->sendMessage(
                                    'Padesh roozane daryaft shod: $' . $status['dollar'] .
                                    ' Dollar va ' . $status['xp'] . ' XP!'
                                );
                            }else{
                                $target->sendMessage('Padesh emrooz ghablan daryaft shode ast.');
                            }
                        }catch(Throwable $e){
                            $this->getLogger()->error('Daily reward claim failed.');
                            $this->getLogger()->logException($e);
                            $target->sendMessage('Daryaft padesh anjam nashod. Lotfan dobare talash konid.');
                        }
                    }elseif($choice === 1){
                        $this->openMainMenu($target);
                    }
                }
            ));
        }catch(Throwable $e){
            $this->getLogger()->error('Unable to load daily reward status.');
            $this->getLogger()->logException($e);
            $player->sendMessage('Vaziyat padesh roozane dar dastres nist.');
        }
    }

    private function openHelpMenu(Player $player) : void{
        $this->sendForm($player, new ArcadeForm(
            'Komak',
            'Dastoorhaye asli:'
                . "\n/arcade - Menu asli"
                . "\n/games - Entekhab bazi"
                . "\n/profile - Profile"
                . "\n/stats - Amar"
                . "\n/balance - Mojoodi Dollar"
                . "\n/daily - Padesh Roozane"
                . "\n/party - Party System"
                . "\n/spawn - Bazgasht be Lobby"
                . "\n/leave - Khorooj az bazi",
            ['Bargasht', 'Bastan'],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    $this->openMainMenu($target);
                }
            }
        ));
    }
    private function showProfile(Player $player) : void{
        $profile = $this->storage?->getProfile($player->getUniqueId()->toString(), $player->getName());
        if($profile === null){
            $player->sendMessage('Profile dar hale hazer dar dastres nist.');
            return;
        }
        $stats = $profile['stats'];
        $best = $stats['best_parkour_ms'] > 0
            ? number_format($stats['best_parkour_ms'] / 1000, 2) . 's'
            : 'Sabt nashode';
        $content = 'Bazikon: ' . $player->getName()
            . "\nSath: " . $profile['level']
            . "\nXP: " . $profile['xp']
            . "\nMojoodi Dollar: $" . $profile['dollar']
            . "\nTedad Bazi: " . $stats['games']
            . "\nTedad Barande: " . $stats['wins']
            . "\nBehtarin Zamane Parkour: " . $best;

        $this->sendForm($player, new ArcadeForm(
            'Profile Shoma',
            $content,
            ['Bazi', 'Bargasht', 'Bastan'],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    $this->openGamesMenu($target);
                }elseif($choice === 1){
                    $this->openMainMenu($target);
                }
            }
        ));
    }

    private function showStatistics(Player $player) : void{
        $profile = $this->storage?->getProfile($player->getUniqueId()->toString(), $player->getName());
        if($profile === null){
            $player->sendMessage('Amar dar hale hazer dar dastres nist.');
            return;
        }
        $stats = $profile['stats'];
        $player->sendMessage('Amar Shoma');
        $player->sendMessage('Bazi ha: ' . $stats['games'] . ' | Barande ha: ' . $stats['wins'] . ' | XP: ' . $profile['xp']);
        $player->sendMessage('Behtarin Zamane Parkour: ' . (
            $stats['best_parkour_ms'] > 0
                ? number_format($stats['best_parkour_ms'] / 1000, 2) . 's'
                : 'Sabt nashode'
        ));
    }

    private function showBalance(Player $player) : void{
        $balance = $this->wallet?->getBalance($player->getUniqueId()->toString(), $player->getName());
        if($balance === null){
            $player->sendMessage('Mojoodi dar hale hazer dar dastres nist.');
            return;
        }
        $player->sendMessage('Mojoodi Shoma: $' . $balance);
    }

    private function handlePartyCommand(Player $player, array $args) : void{
        $manager = $this->partyManager;
        if($manager === null){ $player->sendMessage('Party System dar dastres nist.'); return; }
        $manager->cleanupExpiredInvites();
        $uuid = $player->getUniqueId()->toString();
        $action = strtolower((string) ($args[0] ?? 'menu'));
        $targetName = trim((string) ($args[1] ?? ''));
        if($action === 'create'){
            $created = $manager->create($uuid);
            $player->sendMessage($created !== null ? 'Party shoma ijad shod!' : 'Shoma ghablan ozv yek Party hastid.');
            $this->openPartyMenu($player);
            return;
        }
        if($action === 'invite'){
            $target = $this->findOnlinePlayer($targetName);
            if($target === null || !$manager->invite($uuid, $target->getUniqueId()->toString())){
                $player->sendMessage('Invite anjam nashod. Esm online player ra check konid va motmaen shavid Leader hastid.');
                return;
            }
            $player->sendMessage('Invite baraye ' . $target->getName() . ' ersal shod.');
            $target->sendMessage($player->getName() . ' shoma ra be Party davat kard. /party accept ya /party ra bezanid.');
            return;
        }
        if($action === 'accept'){
            $party = $manager->getPendingInviteParty($uuid);
            if($party === null || !$manager->accept($uuid)){
                $player->sendMessage('Invite vojood nadarad, expire shode ya shoma ozv yek Party hastid.');
                return;
            }
            foreach($party->getMembers() as $memberUuid){
                $this->findOnlinePlayerByUuid($memberUuid)?->sendMessage($player->getName() . ' be Party ezafe shod.');
            }
            $this->openPartyMenu($player);
            return;
        }
        if($action === 'decline'){
            $player->sendMessage($manager->declineInvite($uuid) ? 'Invite rad shod.' : 'Invite baraye rad kardan vojood nadarad.');
            return;
        }
        if($action === 'leave'){
            $party = $manager->getPartyFor($uuid);
            if($party === null || !$manager->leave($uuid)){ $player->sendMessage('Shoma ozv hich Party nistid.'); return; }
            if(!$party->isEmpty()){
                $note = 'Party: ' . $player->getName() . ' kharej shod.';
                foreach($party->getMembers() as $memberUuid){ $this->findOnlinePlayerByUuid($memberUuid)?->sendMessage($note); }
            }
            $player->sendMessage('Az Party kharej shodid.');
            return;
        }
        if($action === 'kick' || $action === 'leader'){
            $target = $this->findOnlinePlayer($targetName);
            $targetUuid = $target?->getUniqueId()->toString() ?? '';
            if($target === null){
                $player->sendMessage('Bazikon online peyda nashod.');
                return;
            }
            $ok = $action === 'kick'
                ? $manager->kick($uuid, $targetUuid)
                : $manager->transferLeadership($uuid, $targetUuid);
            if(!$ok){
                $player->sendMessage('Amaliyat anjam nashod. Faghat Leader mitavanad Party ra modiriat konad.');
                return;
            }
            $note = $action === 'kick'
                ? $target->getName() . ' az Party hazf shod.'
                : $target->getName() . ' Leader jadid Party shod.';
            if($action === 'kick'){ $target->sendMessage('Shoma az Party hazf shodid.'); }
            foreach(($manager->getPartyFor($uuid)?->getMembers() ?? []) as $memberUuid){
                $this->findOnlinePlayerByUuid($memberUuid)?->sendMessage($note);
            }
            return;
        }
        if($action === 'chat'){
            $party = $manager->getPartyFor($uuid);
            $message = trim(implode(' ', array_slice($args, 1)));
            if($party === null || $message === '' || strlen($message) > 180 || preg_match('/[^\x20-\x7E]/', $message) === 1){
                $player->sendMessage('Party chat: payam bayad Finglish va 1 ta 180 character bashad va shoma ozv Party bashid.');
                return;
            }
            $line = '§d[Party] §f' . $player->getName() . ': ' . $message;
            foreach($party->getMembers() as $memberUuid){ $this->findOnlinePlayerByUuid($memberUuid)?->sendMessage($line); }
            return;
        }
        $this->openPartyMenu($player);
    }

    private function openPartyMenu(Player $player) : void{
        $manager = $this->partyManager;
        if($manager === null){ return; }
        $manager->cleanupExpiredInvites();
        $uuid = $player->getUniqueId()->toString();
        $party = $manager->getPartyFor($uuid);
        if($party === null){
            $invite = $manager->getPendingInviteParty($uuid);
            $content = $invite !== null
                ? 'Shoma invite az ' . $this->getOnlinePlayerName($invite->getLeaderUuid()) . ' darid.'
                : 'Ba doostane khod yek Party besazid ya az yek Leader invite daryaft konid.';
            $this->sendForm($player, new ArcadeForm(
                'Party System',
                $content,
                ['Create Party', 'Accept Invite', 'Decline Invite', 'Bargasht'],
                function(Player $target, int $choice) : void{
                    if($choice === 0){ $this->handlePartyCommand($target, ['create']); }
                    elseif($choice === 1){ $this->handlePartyCommand($target, ['accept']); }
                    elseif($choice === 2){ $this->handlePartyCommand($target, ['decline']); }
                    elseif($choice === 3){ $this->openMainMenu($target); }
                }
            ));
            return;
        }
        $lines = [];
        foreach($party->getMembers() as $memberUuid){
            $lines[] = ($party->isLeader($memberUuid) ? 'Leader: ' : 'Member: ') . $this->getOnlinePlayerName($memberUuid);
        }
        $content = 'Members: ' . $party->getMemberCount() . '/' . $party->getMaximumMembers()
            . "\n" . implode("\n", $lines)
            . "\nInvite: /party invite <player>"
            . "\nChat: /party chat <message>";
        if($party->isLeader($uuid)){
            $content .= "\nManage: /party kick <player> | /party leader <player>";
        }
        $this->sendForm($player, new ArcadeForm(
            'Party System',
            $content,
            ['Refresh', 'Leave Party', 'Bargasht'],
            function(Player $target, int $choice) : void{
                if($choice === 0){ $this->openPartyMenu($target); }
                elseif($choice === 1){ $this->handlePartyCommand($target, ['leave']); }
                elseif($choice === 2){ $this->openMainMenu($target); }
            }
        ));
    }

    private function handlePartyDisconnect(string $uuid) : void{
        $manager = $this->partyManager;
        $party = $manager?->getPartyFor($uuid);
        if($manager === null || $party === null){ return; }
        $wasLeader = $party->isLeader($uuid);
        $manager->leave($uuid);
        if(!$party->isEmpty()){
            $message = $wasLeader
                ? 'Party: Leader disconnect shod. ' . $this->getOnlinePlayerName($party->getLeaderUuid()) . ' Leader jadid ast.'
                : 'Party: ' . $this->getOnlinePlayerName($uuid) . ' disconnect shod.';
            foreach($party->getMembers() as $memberUuid){ $this->findOnlinePlayerByUuid($memberUuid)?->sendMessage($message); }
        }
    }

    private function findOnlinePlayer(string $name) : ?Player{
        if($name === '' || strlen($name) > 32){ return null; }
        foreach($this->getServer()->getOnlinePlayers() as $player){
            if(strcasecmp($player->getName(), $name) === 0){ return $player; }
        }
        return null;
    }

    private function findOnlinePlayerByUuid(string $uuid) : ?Player{
        foreach($this->getServer()->getOnlinePlayers() as $player){
            if($player->getUniqueId()->toString() === $uuid){ return $player; }
        }
        return null;
    }

    private function getOnlinePlayerName(string $uuid) : string{
        return $this->findOnlinePlayerByUuid($uuid)?->getName() ?? ('Offline-' . substr($uuid, 0, 8));
    }

    private function sendForm(Player $player, ArcadeForm $form) : void{
        $uuid = $player->getUniqueId()->toString();
        $now = microtime(true);
        if(isset($this->lastFormAt[$uuid]) && ($now - $this->lastFormAt[$uuid]) < 0.35){
            $player->sendActionBarMessage('Lotfan baraye baz kardan menu kami sabr konid.');
            return;
        }
        $this->lastFormAt[$uuid] = $now;
        $player->sendForm($form);
    }
}
