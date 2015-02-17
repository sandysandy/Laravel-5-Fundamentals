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

	public function __construct()
	{
		$this->middleware('auth', ['except' => 'index']);
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
		$article = Auth::user()->articles()->create($request->all());

		$article->tags()->attach($request->get('tag_list'));

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

		return redirect('articles');
	}

}
