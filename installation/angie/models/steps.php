<?php
/**
 * @package angi4j
 * @copyright Copyright (C) 2009-2014 Nicholas K. Dionysopoulos. All rights reserved.
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieModelSteps extends AModel
{
	/**
	 * The default steps to be used in this installer
	 *
	 * @var array
	 */
	private $defaultSteps = array(
		'main'		=> null,
		'database'	=> array(),
		'setup'		=> null,
		'finalise'	=> null,
	);

	/**
	 * The actual steps and substeps I am going to be using
	 *
	 * @var string
	 */
	private $steps = array();


	/**
	 * Reset the steps
	 */
	public function resetSteps()
	{
		$this->steps = $this->defaultSteps;
		ASession::getInstance()->remove('steps.allsteps');
		ASession::getInstance()->remove('databases.dbini');
	}

	/**
	 * Returns the steps array
	 *
	 * @return array
	 */
	public function getSteps()
	{
		if(empty($this->steps))
		{
			// First try fetching the steps from the session
			$this->steps = ASession::getInstance()->get('steps.allsteps', null);
			if (empty($this->steps))
			{
				// No steps are saved in the session. Initialise the steps.
				$this->initialiseSteps();
				ASession::getInstance()->set('steps.allsteps', $this->steps);
			}
		}

		return $this->steps;
	}

	/**
	 * Initialises the steps array
	 */
	public function initialiseSteps()
	{
		$this->steps = $this->defaultSteps;

		$data = $this->input->getData();
		$this->steps['database'] = AModel::getAnInstance('Database', 'AngieModel')->getDatabaseNames();

		// Do I have off-site directories?
        $offsitedirs = AModel::getAnInstance('Offsitedirs', 'AngieModel')->getDirs();

        if($offsitedirs)
        {
            // Rearrange steps order to inject off-site dirs restore after the db step
            $savedSteps = $this->steps;

            $this->steps = array();

            $this->steps['main']        = $savedSteps['main'];
            $this->steps['database']    = $savedSteps['database'];
            $this->steps['offsitedirs'] = $offsitedirs;
            $this->steps['setup']       = $savedSteps['setup'];
            $this->steps['finalise']    = $savedSteps['finalise'];
        }

		// Do I have a site setup step?
		$fileNameMain = APATH_INSTALLATION . '/angie/controllers/setup.php';
		$fileNameAlt = APATH_INSTALLATION . '/angie/platform/controllers/setup.php';

		if (!@file_exists($fileNameAlt) && !@file_exists($fileNameMain))
		{
			unset($this->steps['setup']);
		}

		$this->input->setData($data);
	}

	/**
	 * Gets the currently active step
	 *
	 * @return string
	 */
	public function getActiveStep()
	{
		$steps = $this->getSteps();

		$view = AApplication::getInstance()->getInput()->getCmd('step', '');
		$keys = array_keys($steps);

		if(!in_array($view, $keys))
		{
			$view = array_shift($keys);
		}

		return $view;
	}

	/**
	 * Gets the currently active substep
	 *
	 * @return string
	 */
	public function getActiveSubstep()
	{
		$steps = $this->getSteps();

		$activeStep = $this->getActiveStep();

		$keys = $steps[$activeStep];

		if(empty($keys))
		{
			return null;
		}

		$cursubstep = AApplication::getInstance()->getInput()->getCmd('substep', null);

		if(!in_array($cursubstep, $keys))
		{
			$cursubstep = array_shift($keys);
		}

		return $cursubstep;
	}

	/**
	 * Returns the total number of the substeps in the current step
	 *
	 * @return  int
	 */
	public function getNumberOfSubsteps()
	{
		$step = $this->getActiveStep();
		$substeps = $this->steps[$step];

		if (empty($substeps))
		{
			return 0;
		}
		else
		{
			return count($substeps);
		}
	}

	/**
	 * Finds the next step
	 *
	 * @return array
	 */
	public function getNextStep()
	{
		$steps = $this->getSteps();
		$current = $this->getActiveStep();
		$substeps = $steps[$current];
		$substep = null;

		// Find the next substep. If we are at the last step, null $substeps to
		// force ANGIE to proceed to the next step
		if (!empty($substeps))
		{
			$step = $current;
			$cursubstep = $this->getActiveSubstep();

			$pos = array_search($cursubstep, $substeps);
			if($pos === false)
			{
				$substep = array_shift($substeps);
			}
			else
			{
				if ($pos == count($substeps) - 1)
				{
					// That was the last element. We have to go to the next step.
					$substep = null;
					$substeps = null;
				}
				else
				{
					$substep = $substeps[$pos + 1];
				}
			}
		}

		// Find the next step
		if (empty($substeps))
		{
			// Get the step names
			$keys = array_keys($steps);
			// Find the current step
			$pos = array_search($current, $keys);
			if ($pos === false)
			{
				// We don't have a current step so the next step is the first one
				$step = array_shift($keys);
			}
			else
			{
				if ($pos == count($keys) - 1)
				{
					// We are at the end of all possible steps
					$step = null;
				}
				else
				{
					// Get the next step
					$step = $keys[$pos + 1];
				}
			}
		}

		return array(
			'step'		=> $step,
			'substep'	=> $substep,
		);
	}

	/**
	 * Finds the previous step
	 *
	 * @return array
	 */
	public function getPreviousStep()
	{
		$steps = $this->getSteps();
		$current = $this->getActiveStep();
		$substeps = $steps[$current];
		$substep = '';

		// Find the previous substep. If we are at the first step, null $substeps to
		// force ANGIE to proceed to the previous step
		if (!empty($substeps))
		{
			$step = $current;
			$cursubstep = $this->getActiveSubstep();
			$pos = array_search($cursubstep, $substeps);
			if($pos === false)
			{
				$substep = array_shift($substeps);
			}
			else
			{
				if ($pos == 0)
				{
					// That was the first element. We have to go to the previous step.
					$substep = null;
					$substeps = null;
				}
				else
				{
					$substep = $substeps[$pos - 1];
				}
			}
		}

		// Find the previous step
		if (empty($substeps))
		{
			// Get the step names
			$keys = array_keys($steps);
			// Find the current step
			$pos = array_search($current, $keys);
			if ($pos === false)
			{
				// We don't have a current step so the previous step is the first one
				$step = array_shift($keys);
			}
			else
			{
				if ($pos == 0)
				{
					// We are at the beginning of all possible steps
					$step = null;
				}
				else
				{
					// Get the previous step
					$step = $keys[$pos - 1];
				}
			}
		}

		// Find the last substep in the detected step. If we are at the first
		// step, bail out.
		if (empty($substeps) && empty($steps))
		{
			$substeps = $steps[$step];
			if (!empty($substeps))
			{
				$substep = end($substeps);
			}
		}


		return array(
			'step'		=> $step,
			'substep'	=> $substep,
		);
	}

	public function getBreadCrumbs()
	{
		$steps = $this->getSteps();
		$activeStep = $this->getActiveStep();
		$activeSubstep = $this->getActiveSubstep();

		$ret = array();

		foreach ($steps as $step => $substeps)
		{
			$element = array(
				'name'				=> $step,
				'substeps'			=> count($substeps),
				'active'			=> false,
				'active_substep'	=> 0,
			);

			if ($activeStep == $step)
			{
				$element['active'] = true;
				if (!empty($substeps))
				{
					$pos = array_search($activeSubstep, $substeps);
					if ($pos !== false)
					{
						$element['active_substep'] = $pos + 1;
					}
				}
			}

			$ret[] = $element;
		}

		return $ret;
	}
}