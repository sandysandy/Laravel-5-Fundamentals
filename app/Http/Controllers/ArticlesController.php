<?php namespace App\Http\Controllers;

use App\Article;
use App\Http\Requests;
use App\Http\Requests\ArticleRequest;
use Illuminate\HttpResponse;
use App\Http\Controllers\Controller;
use Auth;
use App\Tag;

class ArticlesController extends Controller
{

	/**
	 * Create a new articles controller instance.
	 */
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['index', 'show']]);
	}

	/**
	 * Show all articles
	 *
	 * @return Response
	 */
	public function index()
	{
		$articles = Article::latest('published_at')->published()->get();

		return view('articles.index', compact('articles'));
	}

	/**
	 * Show a single article
	 *
	 * @param Article $article
	 * @return  Response
	 */

	public function show(Article $article)
	{
		return view('articles.show', compact('article'));
	}

	/**
	 * Show the page to create a new article
	 *
	 * @return Response
	 */
	public function create()
	{
		$tags = Tag::lists('name' , 'id');

		return view('articles.create', compact('tags'));
	}

	/**
	 * Save a new article
	 *
	 * @param CreateArticleRequest $request
	 * @return Response
	 */
	public function store(ArticleRequest $request)
	{
		$this->createArticle($request);

		//flash()->success('Your article has been created!');
		flash()->overlay('Your article has been successfully created!', 'Good Job');

		return redirect('articles');
	}

	/**
	 * Edit an existing article
	 *
	 * @param  integer $id
	 * @return Response
	 */
	public function edit(Article $article)
	{
		$tags = Tag::lists('name' , 'id');

		return view('articles.edit', compact('article' , 'tags'));
	}

	/**
	 * update an existing article
	 *
	 * @param  integer $id
	 * @param  ArticleRequest $request
	 * @return Response
	 */
	public function update(Article $article, ArticleRequest $request)
	{
		$article->update($request->all());

		$this->syncTags($article, $request->input('tag_list'));

		return redirect('articles');
	}

	/**
	 * Sync up the list of tags in the database
	 *
	 * @param  Article $article
	 * @param  array   $tags
	 */
	private function syncTags(Article $article, $tags)
	{
		$tagSync = $this->integrityCheckTags($tags);
		$article->tags()->sync((array) $tagSync);
	}

	/**
	 * Check if tags exisit, if not then create them
	 *
	 * @param  $tags
	 * @return array
	 */
	private function integrityCheckTags($tags)
	{

		// extract the input into separate numeric and string arrays
		$currentTags = array_filter($tags, 'is_numeric');		// ["1", "3", "5"]
		$newTags = array_diff($tags, $currentTags);	// ["awesome", "cool"]

		// Create a new tag for each string in the input and update the current tags array
		foreach ($newTags as $newTag)
		{
		  if ($tag = Tag::create(['name' => $newTag]))
		    $currentTags[] = $tag->id;
		}

		return $currentTags;
	}

	/**
	 * Save a new article
	 * @param  ArticleRequest $request
	 * @return mixed
	 */
	private function createArticle(ArticleRequest $request)
	{
		$article = Auth::user()->articles()->create($request->all());

		if(null !== $request->input('tag_list'))
			$this->syncTags($article, $request->input('tag_list'));

		return $article;
	}

}
