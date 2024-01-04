<?php

namespace App\Filament\Resources\FeedResource\Pages;

use App\Filament\Resources\FeedResource;
use App\Models\Feed;
use App\Models\FeedComments;
use App\Models\FeedLike;
use App\Models\Like;
use App\Models\User;
use Filament\Forms;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class Announcement extends Page
{


    use Forms\Concerns\InteractsWithForms;
    protected static string $resource = FeedResource::class;

    protected static string $view = 'filament.resources.feed-resource.pages.announcement';

    public ?array $data = [];
    public $auth;
    public $authName;
    public $designation;
    public $postImage;
    public $post;
    public $postComments;
    public $allComment;
    public $nextComment;
    public $count;
    public $emoji;


    public function detailForm(Form $form): Form
    {
        return $form

            ->schema([

                RichEditor::make('image')->label('')->required()

            ])
            ->statePath('data');
    }

    public function feedCreate()
    {
        $this->postImage = $this->detailForm->getState();
        // dd($this->postImage);
        Feed::create([
            'image' => $this->postImage['image'],

        ]);
        $this->post = Feed::all();

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
        $this->detailForm->fill();

        $recipient = User::all();
        //   dd($recipient);
        Notification::make()
            ->title('Announcement')
            ->sendToDatabase($recipient);
    }

    public function childcomment($id)
    {

        // dd($this->nextComment);
        $feed = FeedComments::find($id);
        // dd($feed);
        FeedComments::create([
            'feed_id' => $feed->feed_id,
            'comment' => $this->nextComment,
            'parent_id' => $feed->id
        ]);
        // dd('none');
        $this->reset([
            'nextComment'
        ]);
    }
    public function displayfeed($id)
    {
        // dd('ki');
        // dd($this->postComments);
        FeedComments::create([
            'feed_id' => $id,
            'comment' => $this->postComments,

        ]);

        $this->reset([
            'postComments'
        ]);
        // dd('done');
    }



    public function FeedLikes($id)

    {


        // dd('done');
        $user = FeedLike::where('user_id', auth()->id())->where('feeds_id', $id)->first();
        $this->count = FeedLike::where('feeds_id', $id)->count();
        //  dd($count);

        if (!$user) {


            FeedLike::create([
                'feeds_id' => $id,
                'user_id' => auth()->id(),

            ]);
        } else {
            $user->delete();
        }
        $this->post = Feed::with('createdBy.employee', 'createdBy.jobInfo.designation', 'feedComment.subfeeds', 'feedLike')->get();
    }

    public function addSpace()

    {
        // dd('ki');
        // Handle adding space logic here
        $this->postComments .= ' ';
    }

    public function sublikes($id)
    {

        $user = Like::where('user_id', auth()->id())->where('feed_comments_id', $id)->first();

        $this->count = Like::where('feed_comments_id', $id)->count();
        //  dd($this->count);

        if (!$user) {
            Like::create([
                'feed_comments_id' => $id,
                'user_id' => auth()->id(),

            ]);
        } else {
            $user->delete();
        }

        $this->post = Feed::with('createdBy.employee', 'createdBy.jobInfo.designation', 'feedComment.subfeeds', 'feedLike')->get();
        // dd('done');

    }




    public function mount(): void
    {
        // $this->count=FeedLike::where('feeds_id',$id)->count();


        $this->allComment = FeedComments::with('createdBy.employee', 'createdBy.jobInfo.designation', 'likesComment')->get();
        //    dd($this->allComment[0]->createdBy->name);


        //    dd($this->b->name);
        $this->post = Feed::with('createdBy.employee', 'createdBy.jobInfo.designation', 'feedComment.subfeeds', 'feedLike')->orderBy('created_at', 'DESC')->get();
        // dd(  $this->post[0]);


        $this->auth = User::whereId(auth()->id())->with('employee')->first();

        $this->designation = User::whereId(auth()->id())->with('jobInfo.designation')->first();
        // dd($this->designation->id);
        $this->detailForm->fill();
        static::authorizeResourceAccess();
    }



    /**
     * Get all form definitions.
     *
     * @return array
     */
    protected function getForms(): array
    {
        return [
            'detailForm',
        ];
    }
}
