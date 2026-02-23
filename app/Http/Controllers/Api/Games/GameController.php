<?php

namespace App\Http\Controllers\Api\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\GameLike;
use App\Models\GamesKey;
use App\Models\Gateway;
use App\Models\Provider;
use App\Models\Wallet;
use App\Traits\Providers\PlayFiverTrait;
use Illuminate\Http\Request;

class GameController extends Controller
{
    use PlayFiverTrait;

    /**
     * @dev venixplataformas
     * Display a listing of the resource.
     */
    public function index()
    {
        $providers = Provider::with(['games', 'games.provider'])
            ->whereHas('games')
            ->orderBy('name', 'desc')
            ->where('status', 1)
            ->get();

        return response()->json(['providers' =>$providers]);
    }

    /**
     * @dev venixplataformas
     * @return \Illuminate\Http\JsonResponse
     */
    public function featured()
    {
        $featured_games = Game::with(['provider'])
                    ->where('is_featured', 1)
                    ->where('status', 1)
                    ->get();

        return response()->json(['featured_games' => $featured_games]);
    }

    /**
     * @dev venixplataformas
     * Store a newly created resource in storage.
     */
    public function toggleFavorite($id)
    {
        if(auth('api')->check()) {
            $checkExist = GameFavorite::where('user_id', auth('api')->id())->where('game_id', $id)->first();
            if(!empty($checkExist)) {
                if($checkExist->delete()) {
                    return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
                }
            }else{
                $gameFavoriteCreate = GameFavorite::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id
                ]);

                if($gameFavoriteCreate) {
                    return response()->json(['status' => true, 'message' => 'Criado com sucesso']);
                }
            }
        }
    }

    /**
     * @dev venixplataformas
     * Store a newly created resource in storage.
     */
    public function toggleLike($id)
    {
        if(auth('api')->check()) {
            $checkExist = GameLike::where('user_id', auth('api')->id())->where('game_id', $id)->first();
            if(!empty($checkExist)) {
                if($checkExist->delete()) {
                    return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
                }
            }else{
                $gameLikeCreate = GameLike::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id
                ]);

                if($gameLikeCreate) {
                    return response()->json(['status' => true, 'message' => 'Criado com sucesso']);
                }
            }
        }
    }

    /**
     * @dev venixplataformas
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $game = Game::with(['categories', 'provider'])->whereStatus(1)->find($id);
        if(!empty($game)) {
            if(auth('api')->check()) {
                $wallet = Wallet::where('user_id', auth('api')->id())->first();
                if($wallet->total_balance > 0) {
                    $game->increment('views');

                    $token = \Helper::MakeToken([
                        'id' => auth('api')->id(),
                        'game' => $game->game_code
                    ]);

                    switch ($game->distribution) {
                        case 'play_fiver':
                            $playfiver = self::playFiverLaunch($game->game_id, $game->only_demo);

                            if(isset($playfiver['launch_url'])) {
                                return response()->json([
                                    'game' => $game,
                                    'gameUrl' => $playfiver['launch_url'],
                                    'token' => $token
                                ]);
                            }
                            
                            return response()->json(['error' => $playfiver['msg'] ?? 'Erro desconhecido', 'status' => false ], 400);

                        default:
                            return response()->json(['error' => 'Provedor não suportado', 'status' => false ], 400);
                    }
                }
                return response()->json(['error' => 'Você precisa ter saldo para jogar', 'status' => false, 'action' => 'deposit' ], 200);
            }
            return response()->json(['error' => 'Você precisa tá autenticado para jogar', 'status' => false ], 400);
        }
        return response()->json(['error' => '', 'status' => false ], 400);
    }

    /**
     * @dev venixplataformas
     * Show the form for editing the specified resource.
     */
    public function allGames(Request $request)
    {
        $query = Game::query();
        $query->with(['provider', 'categories']);

        if (!empty($request->provider) && $request->provider != 'all') {
            $query->where('provider_id', $request->provider);
        }

        if (!empty($request->category) && $request->category != 'all') {
            $query->whereHas('categories', function ($categoryQuery) use ($request) {
                $categoryQuery->where('slug', $request->category);
            });
        }

        if (isset($request->searchTerm) && !empty($request->searchTerm) && strlen($request->searchTerm) > 2) {
            $query->whereLike(['game_code', 'game_name', 'description', 'distribution', 'provider.name'], $request->searchTerm);
        }else{
            $query->orderByRaw('CASE WHEN id = 23 THEN 0 ELSE 1 END');
            $query->orderBy('views', 'desc');
        }

        $games = $query
            ->where('status', 1)
            ->paginate(24)->appends(request()->query());

        return response()->json(['games' => $games]);
    }

    public function webhookPlayFiver(Request $request)
    {
        return self::webhookPlayFiverAPI($request);
    }
}
