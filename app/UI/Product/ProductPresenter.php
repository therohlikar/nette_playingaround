<?php
namespace App\UI\Product;

use Nette;

final class ProductPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private Nette\Database\Explorer $database,
	) {

	}

    public function actionDefault()
    {
        $rows = $database->fetchAll('SELECT * FROM products');

        $this->sendJson($rows);
    }
}