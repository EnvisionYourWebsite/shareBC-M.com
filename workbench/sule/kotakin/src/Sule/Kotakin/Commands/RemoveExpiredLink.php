<?php
namespace Sule\Kotakin\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Sule\Kotakin\Kotakin;
use Sule\Kotakin\Models\DocumentLink;

class RemoveExpiredLink extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'kotakin:removeExpiredLink';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove any expired link and the file (optional).';

	/**
     * The kotakin.
     *
     * @var Sule\Kotakin\Kotakin
     */
    protected $kotakin;

	/**
	 * Create a new command instance.
	 *
	 * @param Sule\Kotakin\Kotakin $kotakin
	 * @return void
	 */
	public function __construct(Kotakin $kotakin)
	{
		parent::__construct();

		$this->kotakin = $kotakin;
	}

	/**
     * Get kotakin.
     *
     * @return Sule\Kotakin\Kotakin
     */
    public function getKotakin()
    {
        return $this->kotakin;
    }

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$withFile = (bool) $this->option('with-file');

		$links = $this->getKotakin()->getDocLink()->newQuery()
					->whereRaw('UNIX_TIMESTAMP(valid_until) > 0')
					->whereRaw('UNIX_TIMESTAMP(valid_until) <= '.time())
					->get();

		if (count($links) > 0) {
			foreach ($links as $link) {
				if ($withFile) {
					$doc = $link->document;

					if (is_object($doc)) {
						$term = $this->getKotakin()->getTerm()->newQuery()
										->where('object_id', '=', $doc->getId())
										->where('is_file', '=', 1)
										->first();

						if (is_object($term)) {
							$shares = $term->shares;

							if (count($shares) > 0) {
								foreach ($shares as $share) {
									$share->delete();
								}
							}

							unset($shares);

							$term->delete();
						}

						unset($term);

						if (is_object($doc->media)) {
							// Remove all thumbs
							$childs = $doc->media->childs;

							if (count($childs) > 0) {
								foreach ($childs as $file) {
									@unlink(public_path().'/'.$file->getAttribute('path').'/'.$file->getAttribute('filename').'.'.$file->getAttribute('extension'));
									$file->delete();
								}
							}

							unset($childs);

							// Remove the original file
        					@unlink(storage_path().'/'.$doc->media->getAttribute('path').'/'.$doc->media->getAttribute('filename').'.'.$doc->media->getAttribute('extension'));
							$doc->media->delete();
						}

						$doc->delete();
					}

					unset($doc);
				}

				$link->delete();
			}
		}
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('with-file', null, InputOption::VALUE_OPTIONAL, 'Remove with the file.', null),
		);
	}

}