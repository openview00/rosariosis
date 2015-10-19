<?php

/**
 * Widgets
 * Essentially used in the Find a Student form
 *
 * @param  string  $item     widget name or 'all' widgets
 * @param  array   &$myextra Search.inc.php extra (HTML, functions...)
 *
 * @return boolean true if Widget loaded, false if insufficient rights or already saved widget
 */
function Widgets( $item, &$myextra = null )
{
	global $extra,
		$_ROSARIO,
		$RosarioModules;

	if ( isset( $myextra ) )
		$extra =& $myextra;

	// save current widgets list inside $_ROSARIO['Widgets'] global var
	if ( !isset( $_ROSARIO['Widgets'] )
		|| !is_array( $_ROSARIO['Widgets'] ) )
		$_ROSARIO['Widgets'] = array();

	if ( !isset( $extra['functions'] )
		|| !is_array( $extra['functions'] ) )
		$extra['functions'] = array();

	// if insufficient rights or already saved widget, exit
	if ( ( User('PROFILE') !== 'admin'
			&& User( 'PROFILE' ) !== 'teacher' )
		|| ( isset( $_ROSARIO['Widgets'][$item] )
			&& $_ROSARIO['Widgets'][$item] ) )
		return false;

	switch ( $item )
	{
		// All Widgets (or almost)
		case 'all':

			$extra['search'] .= '<TR><TD colspan="2"><TABLE class="width-100p">';

			// FJ regroup widgets wrap
			$widget_wrap_header = 
			function( $title )
			{
				return '<TR><TD colspan="2">&nbsp;
				<A onclick="switchMenu(this); return false;" href="#" class="switchMenu">
					<B>' . $title . '</B>
				</A>
				<BR />
				<TABLE class="widefat width-100p cellspacing-0 col1-align-right hide">';
			};

			$widget_wrap_footer = '</TABLE></TD></TR>';

			// Enrollment
			if ( $RosarioModules['Students']
				&& ( !$_ROSARIO['Widgets']['calendar']
					|| !$_ROSARIO['Widgets']['next_year']
					|| !$_ROSARIO['Widgets']['enrolled']
					|| !$_ROSARIO['Widgets']['rolled'] ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Enrollment' ) );

				Widgets( 'calendar', $extra );
				Widgets( 'next_year', $extra );
				Widgets( 'enrolled', $extra );
				Widgets( 'rolled', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Scheduling
			if ( $RosarioModules['Scheduling']
				&& !$_ROSARIO['Widgets']['course']
				&& User('PROFILE') == 'admin' )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Scheduling' ) );

				Widgets( 'course', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Attendance
			if ( $RosarioModules['Attendance']
				&& ( !$_ROSARIO['Widgets']['absences']
					|| !$_ROSARIO['Widgets']['cp_absences'] ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Attendance' ) );

				Widgets( 'absences', $extra );

				Widgets( 'cp_absences', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Grades
			if ( $RosarioModules['Grades']
				&& ( !$_ROSARIO['Widgets']['gpa']
					|| !$_ROSARIO['Widgets']['class_rank']
					|| !$_ROSARIO['Widgets']['letter_grade'] ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Grades' ) );

				Widgets( 'gpa', $extra );
				Widgets( 'class_rank', $extra );
				Widgets( 'letter_grade', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Eligibility
			if ( $RosarioModules['Eligibility']
				&& ( !$_ROSARIO['Widgets']['eligibility']
					|| !$_ROSARIO['Widgets']['activity'] ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Eligibility' ) );

				Widgets( 'eligibility', $extra );
				Widgets( 'activity', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Food Service
			if ( $RosarioModules['Food_Service']
				&& ( !$_ROSARIO['Widgets']['fsa_balance']
					|| !$_ROSARIO['Widgets']['fsa_discount']
					|| !$_ROSARIO['Widgets']['fsa_status']
					|| !$_ROSARIO['Widgets']['fsa_barcode'] ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Food Service' ) );

				Widgets( 'fsa_balance', $extra );
				Widgets( 'fsa_discount', $extra );
				Widgets( 'fsa_status', $extra );
				Widgets( 'fsa_barcode', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Discipline
			if ( $RosarioModules['Discipline']
				&& ( !$_ROSARIO['Widgets']['reporter']
					|| !$_ROSARIO['Widgets']['incident_date']
					|| !$_ROSARIO['Widgets']['discipline_fields'] ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Discipline' ) );

				Widgets( 'reporter', $extra );
				Widgets( 'incident_date', $extra );
				Widgets( 'discipline_fields', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			// Student Billing
			if ( $RosarioModules['Student_Billing']
				&& ( !$_ROSARIO['Widgets']['balance'] )
				&& AllowUse( 'Student_Billing/StudentFees.php' ) )
			{
				$extra['search'] .= $widget_wrap_header( _( 'Student Billing' ) );

				Widgets( 'balance', $extra );

				$extra['search'] .= $widget_wrap_footer;
			}

			$extra['search'] .= '</TABLE></TD></TR>';

		break;

		// User Widgets (configured in My Preferences)
		case 'user':

			$widgets_RET = DBGet( DBQuery( "SELECT TITLE
				FROM PROGRAM_USER_CONFIG
				WHERE USER_ID='" . User( 'STAFF_ID' ) . "'
				AND PROGRAM='WidgetsSearch'" .
				( count( $_ROSARIO['Widgets'] ) ?
					" AND TITLE NOT IN ('" .
						implode( "','", array_keys( $_ROSARIO['Widgets'] ) ) .
					"')" :
					'' )
				) );

			foreach( $widgets_RET as $widget )
				Widgets( $widget['TITLE'], $extra );

		break;

		// Course Widget
		case 'course':
			if ( !$RosarioModules['Scheduling']
				|| User( 'PROFILE' ) !== 'admin' )
				break;

			if ( $_REQUEST['w_course_period_id'] )
			{
				// Course
				if ( $_REQUEST['w_course_period_id_which'] == 'course' )
				{
					$course = DBGet( DBQuery( "SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID
						FROM COURSE_PERIODS cp,COURSES c
						WHERE c.COURSE_ID=cp.COURSE_ID
						AND cp.COURSE_PERIOD_ID='" . $_REQUEST['w_course_period_id'] . "'" ) );

					$extra['FROM'] .= ",SCHEDULE w_ss";

					$extra['WHERE'] .= " AND w_ss.STUDENT_ID=s.STUDENT_ID
						AND w_ss.SYEAR=ssm.SYEAR
						AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID
						AND w_ss.COURSE_ID='" . $course[1]['COURSE_ID'] . "'
						AND ('" . DBDate() . "'
							BETWEEN w_ss.START_DATE
							AND w_ss.END_DATE
							OR w_ss.END_DATE IS NULL)";

					if ( !$extra['NoSearchTerms'] )
						$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Course' ) . ': </b>'.
							$course[1]['COURSE_TITLE'] . '<BR />';
				}
				// Course Period
				else
				{
					$extra['FROM'] .= ",SCHEDULE w_ss";

					$extra['WHERE'] .= " AND w_ss.STUDENT_ID=s.STUDENT_ID
						AND w_ss.SYEAR=ssm.SYEAR
						AND w_ss.SCHOOL_ID=ssm.SCHOOL_ID
						AND w_ss.COURSE_PERIOD_ID='" . $_REQUEST['w_course_period_id'] . "'
						AND ('".DBDate()."'
							BETWEEN w_ss.START_DATE
							AND w_ss.END_DATE
							OR w_ss.END_DATE IS NULL)";

					$course = DBGet( DBQuery( "SELECT c.TITLE AS COURSE_TITLE,cp.TITLE,cp.COURSE_ID
						FROM COURSE_PERIODS cp,COURSES c
						WHERE c.COURSE_ID=cp.COURSE_ID
						AND cp.COURSE_PERIOD_ID='" . $_REQUEST['w_course_period_id'] . "'" ) );

					if ( !$extra['NoSearchTerms'] )
						$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Course Period' ) . ': </b>' .
							$course[1]['COURSE_TITLE'] . ': ' . $course[1]['TITLE'] . '<BR />';
				}
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Course' ) . '
			</TD><TD>
			<DIV id="course_div"></DIV> 
			<A HREF="#" onclick=\'window.open(
					"Modules.php?modname=misc/ChooseCourse.php",
					"",
					"scrollbars=yes,resizable=yes,width=800,height=400"
				); return false;\'>' .
				_( 'Choose' ) .
			'</A>
			</TD></TR>';

		break;

		// Request Widget
		case 'request':
			if ( !$RosarioModules['Scheduling']
				|| User( 'PROFILE' ) !== 'admin' )
				break;

			// PART OF THIS IS DUPLICATED IN PrintRequests.php
			if ( $_REQUEST['request_course_id'] )
			{
				$course = DBGet( DBQuery( "SELECT c.TITLE
					FROM COURSES c
					WHERE c.COURSE_ID='" . $_REQUEST['request_course_id'] . "'" ) );

				// Request
				if ( !$_REQUEST['not_request_course'] )
				{
					$extra['FROM'] .= ",SCHEDULE_REQUESTS sr";

					$extra['WHERE'] .= " AND sr.STUDENT_ID=s.STUDENT_ID
						AND sr.SYEAR=ssm.SYEAR
						AND sr.SCHOOL_ID=ssm.SCHOOL_ID
						AND sr.COURSE_ID='" . $_REQUEST['request_course_id'] . "' ";

					if ( !$extra['NoSearchTerms'] )
						$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Request' ) . ': </b>' .
							$course[1]['TITLE'] . '<BR />';
				}
				// Missing Request
				else
				{
					$extra['WHERE'] .= " AND NOT EXISTS
						(SELECT '' FROM
							SCHEDULE_REQUESTS sr
							WHERE sr.STUDENT_ID=ssm.STUDENT_ID
							AND sr.SYEAR=ssm.SYEAR
							AND sr.COURSE_ID='" . $_REQUEST['request_course_id'] . "' ) ";

					if ( !$extra['NoSearchTerms'] )
						$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Missing Request' ) . ': </b>' .
							$course[1]['TITLE'] . '<BR />';
				}
			}

			$extra['search'] .= '<TR class="st"><TD>
			'. _( 'Request' ) . '
			</TD><TD>
			<DIV id="request_div"></DIV> 
			<A HREF="#" onclick=\'window.open(
					"Modules.php?modname=misc/ChooseRequest.php",
					"",
					"scrollbars=yes,resizable=yes,width=800,height=400"
				); return false;\'>' .
				_( 'Choose' ) .
			'</A>
			</TD></TR>';

		break;

		// Days Absent Widget
		case 'absences':

			if ( !$RosarioModules['Attendance'] )
				break;

			if ( is_numeric( $_REQUEST['absences_low'] )
				&& is_numeric( $_REQUEST['absences_high'] ) )
			{
				if ( $_REQUEST['absences_low'] > $_REQUEST['absences_high'] )
				{
					$temp = $_REQUEST['absences_high'];

					$_REQUEST['absences_high'] = $_REQUEST['absences_low'];

					$_REQUEST['absences_low'] = $temp;
				}

				// set Absences number SQL condition
				if ( $_REQUEST['absences_low'] == $_REQUEST['absences_high'] )
				{
					$absences_sql = " = '" . $_REQUEST['absences_low'] . "'";
				}
				else
				{
					$absences_sql = " BETWEEN '" . $_REQUEST['absences_low'] . "'
						AND '" . $_REQUEST['absences_high'] . "'";
				}

				$extra['WHERE'] .= " AND (SELECT sum(1-STATE_VALUE) AS STATE_VALUE
					FROM ATTENDANCE_DAY ad
					WHERE ssm.STUDENT_ID=ad.STUDENT_ID
					AND ad.SYEAR=ssm.SYEAR
					AND ad.MARKING_PERIOD_ID IN (" . GetChildrenMP( $_REQUEST['absences_term'], UserMP() ) . "))" .
					$absences_sql;

				switch( $_REQUEST['absences_term'] )
				{
					case 'FY':
						$term = _( 'this school year to date' );
					break;

					case 'SEM':
						$term = _( 'this semester to date' );
					break;

					case 'QTR':
						$term = _( 'this marking period to date' );
					break;
				}

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Days Absent' ) . ' ' . $term . ' ' . _( 'Between' ) . ': </b>' .
						$_REQUEST['absences_low'] . ' &amp; ' . $_REQUEST['absences_high'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>' .
			_( 'Days Absent' ) .
			'<BR />
			<label>
				<INPUT type="radio" name="absences_term" value="FY" checked />&nbsp;' .
				_( 'YTD' ) .
			'</label>&nbsp; 
			<label>
				<INPUT type="radio" name="absences_term" value="SEM" />&nbsp;' .
				GetMP( GetParentMP( 'SEM', UserMP() ), 'SHORT_NAME' ) .
			'</label>&nbsp; 
			<label>
				<INPUT type="radio" name="absences_term" value="QTR" />&nbsp;' .
				GetMP( UserMP(), 'SHORT_NAME' ) .
			'</label>
			</TD><TD>' .
			_( 'Between' ) .
			' <INPUT type="text" name="absences_low" size="3" maxlength="5" /> &amp; ' .
			'<INPUT type="text" name="absences_high" size="3" maxlength="5" />
			</TD></TR>';

		break;

		// Course Period Absences Widget
		// for admins only (relies on the Course widget)
		case 'cp_absences':

			if ( !$RosarioModules['Attendance']
				|| User( 'PROFILE' ) !== 'admin' )
				break;

			if ( is_numeric( $_REQUEST['cp_absences_low'] )
				&& is_numeric( $_REQUEST['cp_absences_high'] )
				&& is_numeric( $_REQUEST['w_course_period_id'] ) )
			{
				if ( $_REQUEST['cp_absences_low'] > $_REQUEST['cp_absences_high'] )
				{
					$temp = $_REQUEST['cp_absences_high'];

					$_REQUEST['cp_absences_high'] = $_REQUEST['cp_absences_low'];

					$_REQUEST['cp_absences_low'] = $temp;
				}


				// set Term SQL condition, if not Full Year
				$term_sql = '';

				if ( $_REQUEST['cp_absences_term'] !== 'FY' )
				{
					$term_sql = " AND cast(ap.MARKING_PERIOD_ID as text)
						IN(" . GetChildrenMP( $_REQUEST['cp_absences_term'], UserMP() ) . ")";
				}

				// set Absences number SQL condition
				if ( $_REQUEST['cp_absences_low'] == $_REQUEST['cp_absences_high'] )
				{
					$absences_sql = " = '" . $_REQUEST['cp_absences_low'] . "'";
				}
				else
				{
					$absences_sql = " BETWEEN '" . $_REQUEST['cp_absences_low'] . "'
						AND '" . $_REQUEST['cp_absences_high'] . "'";
				}

				$extra['WHERE'] .= " AND (SELECT count(*)
					FROM ATTENDANCE_PERIOD ap,ATTENDANCE_CODES ac
					WHERE ac.ID=ap.ATTENDANCE_CODE
					AND ac.STATE_CODE='A'
					AND ap.COURSE_PERIOD_ID='" . $_REQUEST['w_course_period_id'] . "'" .
					$term_sql .
					" AND ap.STUDENT_ID=ssm.STUDENT_ID)" .
					$absences_sql;

				switch( $_REQUEST['cp_absences_term'] )
				{
					case 'FY':
						$term = _( 'this school year to date' );
					break;

					case 'SEM':
						$term = _( 'this semester to date' );
					break;

					case 'QTR':
						$term = _( 'this marking period to date' );
					break;
				}

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Course Period Absences' ) . ' ' . $term . ' ' . _( 'Between' ) . ': </b>' .
						$_REQUEST['cp_absences_low'] . ' &amp; ' . $_REQUEST['cp_absences_high'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>' .
			'<span style="cursor: help;" title="' .
			_( 'Use the Choose link of the Course widget (under Scheduling) to select a Course Period.' ) .
			'">' . _( 'Course Period Absences' ) . '*</span>' .
			'<BR />
			<label>
				<INPUT type="radio" name="cp_absences_term" value="FY" checked />&nbsp;' .
				_( 'YTD' ) .
			'</label>&nbsp; 
			<label>
				<INPUT type="radio" name="cp_absences_term" value="SEM" />&nbsp;' .
				GetMP( GetParentMP( 'SEM', UserMP() ), 'SHORT_NAME' ) .
			'</label>&nbsp; 
			<label>
				<INPUT type="radio" name="cp_absences_term" value="QTR" />&nbsp;' .
				GetMP( UserMP(), 'SHORT_NAME' ) .
			'</label>
			</TD><TD>' .
			_( 'Between' ) .
			' <INPUT type="text" name="cp_absences_low" size="3" maxlength="5" /> &amp;' .
			' <INPUT type="text" name="cp_absences_high" size="3" maxlength="5" />
			</TD></TR>';

		break;

		// GPA Widget
		case 'gpa':

			if ( !$RosarioModules['Grades'] )
				break;

			if ( is_numeric( $_REQUEST['gpa_low'] )
				&& is_numeric( $_REQUEST['gpa_high'] ) )
			{
				if ( $_REQUEST['gpa_low'] > $_REQUEST['gpa_high'] )
				{
					$temp = $_REQUEST['gpa_high'];
					$_REQUEST['gpa_high'] = $_REQUEST['gpa_low'];
					$_REQUEST['gpa_low'] = $temp;
				}

				if ( $_REQUEST['list_gpa'] )
				{
					$extra['SELECT'] .= ',sms.CUM_WEIGHTED_FACTOR,sms.CUM_UNWEIGHTED_FACTOR';

					$extra['columns_after']['CUM_WEIGHTED_FACTOR'] = _( 'Weighted GPA' );
					$extra['columns_after']['CUM_UNWEIGHTED_FACTOR'] = _( 'Unweighted GPA' );
				}

				if ( mb_strpos( $extra['FROM'], 'STUDENT_MP_STATS sms' ) === false )
				{
					$extra['FROM'] .= ",STUDENT_MP_STATS sms";

					$extra['WHERE'] .= " AND sms.STUDENT_ID=s.STUDENT_ID
						AND sms.MARKING_PERIOD_ID='" . $_REQUEST['gpa_term'] . "'";
				}

				$extra['WHERE'] .= " AND sms.CUM_" . ( ($_REQUEST['weighted'] == 'Y' ) ? '' : 'UN' ) . "WEIGHTED_FACTOR *
					(SELECT GP_SCALE
						FROM REPORT_CARD_GRADE_SCALES
						WHERE SCHOOL_ID='" . UserSchool() . "'
						AND SYEAR='" . UserSyear() . "')
					BETWEEN '" . $_REQUEST['gpa_low'] . "' AND '" . $_REQUEST['gpa_high'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' .
						( ( $_REQUEST['gpa_weighted'] == 'Y' ) ?
							_( 'Weighted GPA' ) :
							_( 'Unweighted GPA' ) ) .
						' ' . _( 'Between' ) . ': </b>' .
						$_REQUEST['gpa_low'] . ' &amp; ' . $_REQUEST['gpa_high'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'GPA' ) . '
			<BR />
			<label>
				<INPUT type="checkbox" name="weighted" value="Y">&nbsp;' .
				_( 'Weighted' ) .
			'</label>
			<BR />';

			if ( GetMP( $MPfy = GetParentMP( 'FY', GetParentMP( 'SEM', UserMP() ) ), 'DOES_GRADES') == 'Y' )
				$extra['search'] .= '<label>
						<INPUT type="radio" name="gpa_term" value="' . $MPfy . '" checked />&nbsp;' .
						GetMP( $MPfy, 'SHORT_NAME' ) .
					'</label>&nbsp; ';

			if ( GetMP( $MPsem = GetParentMP( 'SEM', UserMP() ), 'DOES_GRADES' ) == 'Y' )
				$extra['search'] .= '<label>
						<INPUT type="radio" name="gpa_term" value="' . $MPsem . '">&nbsp;' .
						GetMP( $MPsem, 'SHORT_NAME' ) .
					'</label> &nbsp;';

			if ( GetMP( $MPtrim = UserMP(), 'DOES_GRADES' ) == 'Y' )
				$extra['search'] .= '<label>
						<INPUT type="radio" name="gpa_term" value="' . $MPtrim . '" checked />&nbsp;' .
						GetMP( $MPtrim, 'SHORT_NAME' ) .
					'</label>';

			$extra['search'] .= '</TD><TD>
			' . _( 'Between' ) .
			' <INPUT type="text" name="gpa_low" size="3" maxlength="5" /> &amp;' .
			' <INPUT type="text" name="gpa_high" size="3" maxlength="5" />
			</TD></TR>';

		break;

		// Class Rank Widget
		case 'class_rank':

			if ( !$RosarioModules['Grades'] )
				break;

			if ( is_numeric( $_REQUEST['class_rank_low'] )
				&& is_numeric( $_REQUEST['class_rank_high'] ) )
			{
				if ( $_REQUEST['class_rank_low'] > $_REQUEST['class_rank_high'] )
				{
					$temp = $_REQUEST['class_rank_high'];
					$_REQUEST['class_rank_high'] = $_REQUEST['class_rank_low'];
					$_REQUEST['class_rank_low'] = $temp;
				}

				if ( mb_strpos( $extra['FROM'], 'STUDENT_MP_STATS sms' ) === false )
				{
					$extra['FROM'] .= ",STUDENT_MP_STATS sms";

					$extra['WHERE'] .= " AND sms.STUDENT_ID=s.STUDENT_ID
						AND sms.MARKING_PERIOD_ID='" . $_REQUEST['class_rank_term'] . "'";
				}

				$extra['WHERE'] .= " AND sms.CUM_RANK BETWEEN
					'" . $_REQUEST['class_rank_low'] . "'
					AND '" . $_REQUEST['class_rank_high'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Class Rank' ) . ' ' . _( 'Between' ) . ': </b>' .
						$_REQUEST['class_rank_low'] . ' &amp; ' . $_REQUEST['class_rank_high'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Class Rank' ) . '
			<BR />';

			if ( GetMP( $MPfy = GetParentMP( 'FY', GetParentMP( 'SEM', UserMP() ) ), 'DOES_GRADES' ) == 'Y' )
				$extra['search'] .= '<label>
						<INPUT type="radio" name="class_rank_term" value="' . $MPfy . '">&nbsp;' .
						GetMP( $MPfy, 'SHORT_NAME' ) .
					'</label>&nbsp; ';

			if ( GetMP( $MPsem = GetParentMP( 'SEM', UserMP() ), 'DOES_GRADES' ) == 'Y' )
				$extra['search'] .= '<label>
						<INPUT type="radio" name="class_rank_term" value="' . $MPsem . '">&nbsp;' .
						GetMP( $MPsem, 'SHORT_NAME' ) .
					'</label> &nbsp; ';

			if ( GetMP( $MPtrim = UserMP(), 'DOES_GRADES' ) == 'Y' )
				$extra['search'] .= '<label>
						<INPUT type="radio" name="class_rank_term" value="' . $MPtrim . '" checked />&nbsp;' .
						GetMP( $MPtrim, 'SHORT_NAME' ) .
					'</label>';

			if ( mb_strlen( $pros = GetChildrenMP( 'PRO', UserMP() ) ) )
			{
				$pros = explode( ',', str_replace( "'", '', $pros ) );

				foreach ( $pros as $pro )
					$extra['search'] .= '<label>
							<INPUT type="radio" name="class_rank_term" value="' . $pro . '">&nbsp;' .
							GetMP( $pro, 'SHORT_NAME' ) .
						'</label> &nbsp;';
			}

			$extra['search'] .= '</TD><TD>
			' . _( 'Between' ) .
			' <INPUT type="text" name="class_rank_low" size="3" maxlength="5" /> &amp;' .
			' <INPUT type="text" name="class_rank_high" size="3" maxlength="5" />
			</TD></TR>';

		break;

		// Report Card Grade Widget
		case 'letter_grade':

			if ( !$RosarioModules['Grades'] )
				break;

			if ( count( $_REQUEST['letter_grade'] ) )
			{
				$LetterGradeSearchTerms = '<b>' . ( $_REQUEST['letter_grade_exclude'] == 'Y' ?
						_( 'Without' ) :
						_( 'With' ) ) .
					' ' . _( 'Report Card Grade' ) . ': </b>';

				$letter_grades_RET = DBGet( DBQuery( "SELECT ID,TITLE
					FROM REPORT_CARD_GRADES
					WHERE SCHOOL_ID='" . UserSchool() . "'
					AND SYEAR='" . UserSyear() . "'"), array(), array( 'ID' ) );

				foreach ( $_REQUEST['letter_grade'] as $grade => $Y )
				{
					$letter_grades .= ",'" . $grade . "'";

					$LetterGradeSearchTerms .= $letter_grades_RET[$grade][1]['TITLE'].', ';
				}

				$LetterGradeSearchTerms = mb_substr( $LetterGradeSearchTerms, 0, -2 ) . '<BR />';

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] = $LetterGradeSearchTerms;

				$extra['WHERE'] .= " AND " . ( $_REQUEST['letter_grade_exclude'] == 'Y' ? 'NOT ' : '' ) . "EXISTS
					(SELECT ''
						FROM STUDENT_REPORT_CARD_GRADES sg3
						WHERE sg3.STUDENT_ID=ssm.STUDENT_ID
						AND sg3.SYEAR=ssm.SYEAR
						AND sg3.REPORT_CARD_GRADE_ID IN (" . mb_substr( $letter_grades, 1 ) . ")
						AND sg3.MARKING_PERIOD_ID='" . $_REQUEST['letter_grade_term'] . "' )";
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Grade' ) . '
			<BR />
			<label>
				<INPUT type="checkbox" name="letter_grade_exclude" value="Y">&nbsp;' . _( 'Did not receive' ) .
			'</label>
			<BR />
			<label class="nobr">
				<INPUT type="radio" name="letter_grade_term" value="' . GetParentMP( 'SEM', UserMP() ) . '" />&nbsp;' .
				GetMP( GetParentMP( 'SEM', UserMP() ), 'SHORT_NAME' ) .
			'</label>&nbsp;
			<label class="nobr">
				<INPUT type="radio" name="letter_grade_term" value="' . UserMP() . '" />&nbsp;' .
				GetMP( UserMP(), 'SHORT_NAME' ) .
			'</label>';

			if ( mb_strlen( $pros = GetChildrenMP( 'PRO', UserMP() ) ) )
			{
				$pros = explode( ',', str_replace( "'", '', $pros ) );

				foreach ( $pros as $pro )
					$extra['search'] .= '<label class="nobr">
							<INPUT type="radio" name="letter_grade_term" value="' . $pro . '" />&nbsp;' .
							GetMP( $pro, 'SHORT_NAME' ) .
						'</label>&nbsp;';
			}

			$extra['search'] .= '</TD><TD>';

			//FJ fix error Invalid argument supplied for foreach()
			if ( !$_REQUEST['search_modfunc'] )
			{
				$letter_grades_RET = DBGet( DBQuery( "SELECT rg.ID,rg.TITLE,rg.GRADE_SCALE_ID 
					FROM REPORT_CARD_GRADES rg,REPORT_CARD_GRADE_SCALES rs 
					WHERE rg.SCHOOL_ID='" . UserSchool() . "' 
					AND rg.SYEAR='" . UserSyear() . "' 
					AND rs.ID=rg.GRADE_SCALE_ID" .
					( User( 'PROFILE' ) == 'teacher' ?
					" AND rg.GRADE_SCALE_ID=
						(SELECT GRADE_SCALE_ID
							FROM COURSE_PERIODS
							WHERE COURSE_PERIOD_ID='" . UserCoursePeriod() . "')" :
					'' ) .
					" ORDER BY rs.SORT_ORDER,rs.ID,rg.BREAK_OFF IS NOT NULL DESC,rg.BREAK_OFF DESC,rg.SORT_ORDER" ),
				array(), array( 'GRADE_SCALE_ID' ) );

				foreach ( $letter_grades_RET as $grades )
				{
					$i = 0;

					foreach ( (array)$grades as $grade )
					{
						$extra['search'] .= '<label>
								<INPUT type="checkbox" value="Y" name="letter_grade[' . $grade['ID'] . ']" />' .
								$grade['TITLE'] .
							'</label>&nbsp; ';

						$i++;
					}
				}
			}

			$extra['search'] .= '</TD></TR>';

		break;

		// Eligibility (Ineligible) Widget
		case 'eligibility':

			if ( !$RosarioModules['Eligibility'] )
				break;

			if ( $_REQUEST['ineligible'] == 'Y' )
			{
				switch ( date( 'D' ) )
				{
					case 'Mon':
						$today = 1;
					break;

					case 'Tue':
						$today = 2;
					break;

					case 'Wed':
						$today = 3;
					break;

					case 'Thu':
						$today = 4;
					break;

					case 'Fri':
						$today = 5;
					break;

					case 'Sat':
						$today = 6;
					break;

					case 'Sun':
						$today = 7;
					break;
				}

				$start_date = mb_strtoupper( date(
					'd-M-y',
					time() - ( $today - ProgramConfig( 'eligibility', 'START_DAY' ) ) * 60 * 60 * 24
				) );

				$end_date = mb_strtoupper( date( 'd-M-y', time() ) );

				$extra['WHERE'] .= " AND (SELECT count(*)
					FROM ELIGIBILITY e
					WHERE ssm.STUDENT_ID=e.STUDENT_ID
					AND e.SYEAR=ssm.SYEAR
					AND e.SCHOOL_DATE BETWEEN '" . $start_date . "'
					AND '" . $end_date . "'
					AND e.ELIGIBILITY_CODE='FAILING') > '0'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Eligibility' ) . ': </b>' .
						_( 'Ineligible' ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			</TD><TD>
			<label>
				<INPUT type="checkbox" name="ineligible" value="Y" />&nbsp;' .
				_( 'Ineligible' ) .
			'</label>
			</TD></TR>';

		break;

		// Activity (Eligibility) Widget
		case 'activity':

			if ( !$RosarioModules['Eligibility'] )
				break;

			if ( $_REQUEST['activity_id'] )
			{
				$extra['FROM'] .= ",STUDENT_ELIGIBILITY_ACTIVITIES sea";

				$extra['WHERE'] .= " AND sea.STUDENT_ID=s.STUDENT_ID
					AND sea.SYEAR=ssm.SYEAR
					AND sea.ACTIVITY_ID='" . $_REQUEST['activity_id'] . "'";

				$activity = DBGet( DBQuery( "SELECT TITLE
					FROM ELIGIBILITY_ACTIVITIES
					WHERE ID='" . $_REQUEST['activity_id'] . "'" ) );

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Activity' ) . ': </b>' .
						$activity[1]['TITLE'] . '<BR />';
			}

			if ( !$_REQUEST['search_modfunc'] )
				$activities_RET = DBGet( DBQuery( "SELECT ID,TITLE
					FROM ELIGIBILITY_ACTIVITIES
					WHERE SCHOOL_ID='" . UserSchool() . "'
					AND SYEAR='" . UserSyear() . "'" ) );

			$select = '<SELECT name="activity_id">
				<OPTION value="">' . _( 'Not Specified' ) . '</OPTION>';

			foreach ( (array)$activities_RET as $activity )
				$select .= '<OPTION value="' . $activity['ID'] . '">' . $activity['TITLE'] . '</OPTION>';

			$select .= '</SELECT>';

			$extra['search'] .= '<TR class="st"><TD>' .
			_( 'Activity' ) .
			'</TD><TD>' .
			$select .
			'</TD></TR>';

		break;

		// Mailing Labels Widget
		case 'mailing_labels':

			if ( $_REQUEST['mailing_labels'] == 'Y' )
			{
				require_once( 'ProgramFunctions/MailingLabel.fnc.php' );

				$extra['SELECT'] .= ',coalesce(sam.ADDRESS_ID,-ssm.STUDENT_ID) AS ADDRESS_ID,
					sam.ADDRESS_ID AS MAILING_LABEL';

				$extra['FROM'] = " LEFT OUTER JOIN STUDENTS_JOIN_ADDRESS sam
					ON (sam.STUDENT_ID=ssm.STUDENT_ID
						AND sam.MAILING='Y'" . ( $_REQUEST['residence'] == 'Y' ? " AND sam.RESIDENCE='Y'" : '' ) . ")" .
					$extra['FROM'];

				$extra['functions'] += array( 'MAILING_LABEL' => 'MailingLabel' );
			}

			$extra['search'] .= '<TR class="st"><TD>' .
				_( 'Mailing Labels' ) .
				'</TD><TD>' .
				'<INPUT type="checkbox" name="mailing_labels" value="Y" />' .
				'</TD>';

		break;

		// Student Billing Balance Widget
		case 'balance':

			if ( !$RosarioModules['Student_Billing']
				|| !AllowUse( 'Student_Billing/StudentFees.php' ) )
				break;

			if ( is_numeric( $_REQUEST['balance_low'] )
				&& is_numeric( $_REQUEST['balance_high'] ) )
			{
				if ( $_REQUEST['balance_low'] > $_REQUEST['balance_high'] )
				{
					$temp = $_REQUEST['balance_high'];
					$_REQUEST['balance_high'] = $_REQUEST['balance_low'];
					$_REQUEST['balance_low'] = $temp;
				}

				$extra['WHERE'] .= " AND (
					coalesce((SELECT sum(p.AMOUNT)
						FROM BILLING_PAYMENTS p
						WHERE p.STUDENT_ID=ssm.STUDENT_ID
						AND p.SYEAR=ssm.SYEAR),0) -
					coalesce((SELECT sum(f.AMOUNT)
						FROM BILLING_FEES f
						WHERE f.STUDENT_ID=ssm.STUDENT_ID
						AND f.SYEAR=ssm.SYEAR),0))
					BETWEEN '" . $_REQUEST['balance_low'] . "'
					AND '" . $_REQUEST['balance_high'] . "' ";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Student Billing Balance' ) . ' ' . _( 'Between' ) .': </b>' .
						$_REQUEST['balance_low'] . ' &amp; ' .
						$_REQUEST['balance_high'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Balance' ) . '
			</TD><TD>
			' . _( 'Between' ) .
			' <INPUT type="text" name="balance_low" size="5" maxlength="10" /> &amp;' .
			' <INPUT type="text" name="balance_high" size="5" maxlength="10" />
			</TD></TR>';

		break;

		// Discipline Reporter Widget
		case 'reporter':

			if ( !$RosarioModules['Discipline'] )
				break;

			$users_RET = DBGet( DBQuery( "SELECT STAFF_ID,FIRST_NAME,LAST_NAME,MIDDLE_NAME 
				FROM STAFF 
				WHERE SYEAR='".UserSyear()."' 
				AND (SCHOOLS IS NULL OR SCHOOLS LIKE '%," . UserSchool() . ",%') 
				AND (PROFILE='admin' OR PROFILE='teacher') 
				ORDER BY LAST_NAME,FIRST_NAME,MIDDLE_NAME"), array(), array( 'STAFF_ID' ) );

			if ( $_REQUEST['discipline_reporter'] )
			{
				if ( mb_strpos( $extra['FROM'], 'DISCIPLINE_REFERRALS' ) === false )
				{
					$extra['WHERE'] .= ' AND dr.STUDENT_ID=ssm.STUDENT_ID
						AND dr.SYEAR=ssm.SYEAR
						AND dr.SCHOOL_ID=ssm.SCHOOL_ID ';

					$extra['FROM'] .= ',DISCIPLINE_REFERRALS dr ';
				}

				$extra['WHERE'] .= " AND dr.STAFF_ID='" . $_REQUEST['discipline_reporter'] . "' ";

				$reporter = $users_RET[$_REQUEST['discipline_reporter']][1];

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Reporter' ) . ': </b>' .
						$reporter['LAST_NAME'] . ', ' .
						$reporter['FIRST_NAME'] . ' ' .
						$reporter['MIDDLE_NAME'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Reporter' ) . '
			</TD><TD>
			<SELECT name="discipline_reporter">
				<OPTION value="">' . _( 'Not Specified' ) . '</OPTION>';

			foreach ( (array)$users_RET as $id => $user )
				$extra['search'] .= '<OPTION value="' . $id . '"">' .
						$user[1]['LAST_NAME'] . ', ' .
						$user[1]['FIRST_NAME'] . ' ' .
						$user[1]['MIDDLE_NAME'] .
					'</OPTION>';

			$extra['search'] .= '</SELECT>';

			$extra['search'] .= '</TD></TR>';

		break;

		// Discipline Incident Date Widget
		case 'incident_date':

			if ( !$RosarioModules['Discipline'] )
				break;

			// Verify begin date
			if ( $_REQUEST['month_discipline_entry_begin']
				&& $_REQUEST['day_discipline_entry_begin']
				&& $_REQUEST['year_discipline_entry_begin'] )
			{
				$_REQUEST['discipline_entry_begin'] = $_REQUEST['day_discipline_entry_begin'] . '-' .
					$_REQUEST['month_discipline_entry_begin'] . '-' .
					$_REQUEST['year_discipline_entry_begin'];

				if ( !VerifyDate( $_REQUEST['discipline_entry_begin'] ) )
					unset($_REQUEST['discipline_entry_begin']);

				unset( $_REQUEST['day_discipline_entry_begin'] );
				unset( $_REQUEST['month_discipline_entry_begin'] );
				unset( $_REQUEST['year_discipline_entry_begin'] );
			}

			// Verify end date
			if ( $_REQUEST['month_discipline_entry_end']
				&& $_REQUEST['day_discipline_entry_end']
				&& $_REQUEST['year_discipline_entry_end'] )
			{
				$_REQUEST['discipline_entry_end'] = $_REQUEST['day_discipline_entry_end'] . '-' .
					$_REQUEST['month_discipline_entry_end'] . '-' .
					$_REQUEST['year_discipline_entry_end'];

				if ( !VerifyDate( $_REQUEST['discipline_entry_end'] ) )
					unset( $_REQUEST['discipline_entry_end'] );

				unset( $_REQUEST['day_discipline_entry_end'] );
				unset( $_REQUEST['month_discipline_entry_end'] );
				unset( $_REQUEST['year_discipline_entry_end'] );
			}

			if ( ( $_REQUEST['discipline_entry_begin']
					|| $_REQUEST['discipline_entry_end'] )
				&& mb_strpos( $extra['FROM'], 'DISCIPLINE_REFERRALS' ) === false  )
			{
				$extra['WHERE'] .= ' AND dr.STUDENT_ID=ssm.STUDENT_ID
					AND dr.SYEAR=ssm.SYEAR
					AND dr.SCHOOL_ID=ssm.SCHOOL_ID ';

				$extra['FROM'] .= ',DISCIPLINE_REFERRALS dr ';
			}

			if ( $_REQUEST['discipline_entry_begin']
				&& $_REQUEST['discipline_entry_end'] )
			{
				$extra['WHERE'] .= " AND dr.ENTRY_DATE
					BETWEEN '" . $_REQUEST['discipline_entry_begin'] .
					"' AND '" . $_REQUEST['discipline_entry_end'] . "' ";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Incident Date' ) . ' ' . _( 'Between' ) . ': </b>' .
						ProperDate( $_REQUEST['discipline_entry_begin'] ) . ' &amp; ' .
						ProperDate( $_REQUEST['discipline_entry_end'] ) . '<BR />';
			}
			elseif ( $_REQUEST['discipline_entry_begin'] )
			{
				$extra['WHERE'] .= " AND dr.ENTRY_DATE>='" . $_REQUEST['discipline_entry_begin'] . "' ";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Incident Date' ) . ' ' . _( 'On or After' ) . ' </b>' .
						ProperDate( $_REQUEST['discipline_entry_begin'] ) . '<BR />';
			}
			elseif ( $_REQUEST['discipline_entry_end'] )
			{
				$extra['WHERE'] .= " AND dr.ENTRY_DATE<='" . $_REQUEST['discipline_entry_end'] . "' ";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Incident Date' ) . ' ' . _( 'On or Before' ) . ' </b>' .
						ProperDate( $_REQUEST['discipline_entry_end'] ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Incident Date' ) . '
			</TD><TD>
			<table class="cellspacing-0"><tr><td>
			<span class="sizep2">&ge;</span>&nbsp;
			</td><td>
			' . PrepareDate( '', '_discipline_entry_begin', true, array( 'short' => true ) ).'
			</td></tr><tr><td>
			<span class="sizep2">&le;</span>&nbsp;
			</td><td>
			' . PrepareDate( '', '_discipline_entry_end', true, array( 'short' => true ) ).'
			</td></tr></table>
			</TD></TR>';

		break;

		// Discipline Fields Widgets
		case 'discipline_fields':

			if ( !$RosarioModules['Discipline'] )
				break;

			if ( isset( $_REQUEST['discipline'] )
				&& is_array( $_REQUEST['discipline'] ) )
			{
				//modify loop: use for instead of foreach
				$key = array_keys( $_REQUEST['discipline'] );
				$size = sizeOf( $key );

				for ( $i = 0; $i < $size; $i++ )
					if ( !( $_REQUEST['discipline'][$key[$i]] ) )
					{
						unset( $_REQUEST['discipline'][$key[$i]] );
					}

				/*foreach($_REQUEST['discipline'] as $key=>$value)
				{
					if(!$value)
						unset($_REQUEST['discipline'][$key]);
				}*/
			}

			//FJ bugfix wrong advanced student search results, due to discipline numeric fields
			if ( isset( $_REQUEST['discipline_begin'] )
				&& is_array( $_REQUEST['discipline_begin'] ) )
			{
				//modify loop: use for instead of foreach
				$key = array_keys( $_REQUEST['discipline_begin'] );
				$size = sizeOf( $key );

				for ( $i = 0; $i < $size; $i++ )
					if ( !( $_REQUEST['discipline_begin'][$key[$i]] )
						|| !is_numeric( $_REQUEST['discipline_begin'][$key[$i]] ) )
					{
						unset( $_REQUEST['discipline_begin'][$key[$i]] );
					}

				/*foreach($_REQUEST['discipline_begin'] as $key=>$value)
				{
					if(!$value)
						unset($_REQUEST['discipline_begin'][$key]);
				}*/
			}

			if ( isset( $_REQUEST['discipline_end'] )
				&& is_array( $_REQUEST['discipline_end'] ) )
			{
				//modify loop: use for instead of foreach
				$key = array_keys( $_REQUEST['discipline_end'] );
				$size = sizeOf( $key );

				for ( $i = 0; $i < $size; $i++ )
					if ( !( $_REQUEST['discipline_end'][$key[$i]] )
						|| !is_numeric( $_REQUEST['discipline_end'][$key[$i]] ) )
					{
						unset( $_REQUEST['discipline_end'][$key[$i]] );
					}

				/*foreach($_REQUEST['discipline_end'] as $key=>$value)
				{
					if(!$value)
						unset($_REQUEST['discipline_end'][$key]);
				}*/
			}

			if ( ( count( $_REQUEST['discipline'] )
					|| count( $_REQUEST['discipline_begin'] )
					|| count( $_REQUEST['discipline_end'] ) )
				&& mb_strpos( $extra['FROM'], 'DISCIPLINE_REFERRALS' ) === false )
			{
				$extra['WHERE'] .= ' AND dr.STUDENT_ID=ssm.STUDENT_ID
					AND dr.SYEAR=ssm.SYEAR
					AND dr.SCHOOL_ID=ssm.SCHOOL_ID ';

				$extra['FROM'] .= ',DISCIPLINE_REFERRALS dr ';
			}

			$categories_RET = DBGet( DBQuery( "SELECT f.ID,u.TITLE,f.DATA_TYPE,u.SELECT_OPTIONS
				FROM DISCIPLINE_FIELDS f,DISCIPLINE_FIELD_USAGE u
				WHERE u.DISCIPLINE_FIELD_ID=f.ID
				AND u.SYEAR='" . UserSyear() . "'
				AND u.SCHOOL_ID='" . UserSchool() . "'
				AND f.DATA_TYPE!='textarea'
				AND f.DATA_TYPE!='date'" ) );

			foreach( (array)$categories_RET as $category )
			{
				$extra['search'] .= '<TR class="st"><TD>' . $category['TITLE'] . '</TD><TD>';

				switch ( $category['DATA_TYPE'] )
				{
					case 'text':

						$extra['search'] .= '<INPUT type="text" name="discipline[' . $category['ID'] . ']" />';

						if ( $_REQUEST['discipline'][$category['ID']] )
						{
							$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
								" LIKE '" . $_REQUEST['discipline'][$category['ID']] . "%' ";

							if ( !$extra['NoSearchTerms'] )
								$_ROSARIO['SearchTerms'] .= '<b>' . $category['TITLE'] . ': </b> ' .
									$_REQUEST['discipline'][$category['ID']] . '<BR />';
						}

					break;

					case 'checkbox':

						$extra['search'] .= '<INPUT type="checkbox" name="discipline[' . $category['ID'] . ']" value="Y" />';

						if ( $_REQUEST['discipline'][$category['ID']] )
						{
							$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] . " = 'Y' ";

							if ( !$extra['NoSearchTerms'] )
								$_ROSARIO['SearchTerms'] .= '<b>' . $category['TITLE'] . '</b><BR />';

						}

					break;

					case 'numeric':

						$extra['search'] .= _( 'Between' ) .
							' <INPUT type="text" name="discipline_begin[' . $category['ID'] . ']" size="3" maxlength="11" /> &amp;' .
							' <INPUT type="text" name="discipline_end[' . $category['ID'] . ']" size="3" maxlength="11" />';

						if ( $_REQUEST['discipline_begin'][$category['ID']] && $_REQUEST['discipline_end'][$category['ID']] )
						{
							$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
								" BETWEEN '" . $_REQUEST['discipline_begin'][$category['ID']] .
								"' AND '" . $_REQUEST['discipline_end'][$category['ID']] . "' ";

							if ( !$extra['NoSearchTerms'] )
								$_ROSARIO['SearchTerms'] .= '<b>' . $category['TITLE'] . ' ' . _('Between') . ': </b>' .
									$_REQUEST['discipline_begin'][$category['ID']] . ' &amp; ' .
									$_REQUEST['discipline_end'][$category['ID']].'<BR />';
						}

					break;

					case 'multiple_checkbox':
					case 'multiple_radio':
					case 'select':

						$category['SELECT_OPTIONS'] = explode( '<br />', nl2br( $category['SELECT_OPTIONS'] ) );

						$extra['search'] .= '<SELECT name="discipline[' . $category['ID'] . ']">
							<OPTION value="">' . _( 'N/A' ) . '</OPTION>';

						foreach ( (array)$category['SELECT_OPTIONS'] as $option )
							$extra['search'] .= '<OPTION value="' . $option . '">' . $option . '</OPTION>';

						$extra['search'] .= '</SELECT>';

						if ( $_REQUEST['discipline'][$category['ID']] )
						{
							if ( $category['DATA_TYPE'] == 'multiple_radio'
								|| $category['DATA_TYPE'] == 'select' )
							{
								$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
									" = '" . $_REQUEST['discipline'][$category['ID']] . "' ";
							}
							elseif ( $category['DATA_TYPE'] == 'multiple_checkbox' )
							{
								$extra['WHERE'] .= " AND dr.CATEGORY_" . $category['ID'] .
									" LIKE '%||" . $_REQUEST['discipline'][$category['ID']] . "||%' ";
							}

							if( !$extra['NoSearchTerms'] )
								$_ROSARIO['SearchTerms'] .= '<b>' . $category['TITLE'] . ': </b>' .
									$_REQUEST['discipline'][$category['ID']] . '<BR />';
						}

					break;
				}

				$extra['search'] .= '</TD></TR>';
			}

		break;

		// Next Year (Enrollment) Widget
		case 'next_year':

			if ( !$RosarioModules['Students'] )
				break;

			$schools_RET = DBGet( DBQuery( "SELECT ID,TITLE
				FROM SCHOOLS
				WHERE ID!='" . UserSchool() . "'
				AND SYEAR='" . UserSyear() . "'"), array(), array( 'ID' ) );

			$next_year_options = array(
				'' => _( 'N/A' ),
				'!' => _( 'No Value' ),
				UserSchool() => _( 'Next grade at current school' ),
				'0' => _( 'Retain' ),
				'-1' => _( 'Do not enroll after this school year' ),
			);

			foreach ( $schools_RET as $id => $school )
				$next_year_options[$id] = $school[1]['TITLE'];

			if ( $_REQUEST['next_year'] )
			{
				if ( $_REQUEST['next_year'] == '!' )
				{
					$extra['WHERE'] .= " AND ssm.NEXT_SCHOOL IS NULL";
				}
				else
				{
					$extra['WHERE'] .= " AND ssm.NEXT_SCHOOL='" . $_REQUEST['next_year'] . "'";
				}

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Next Year' ) . ': </b>' .
						$next_year_options[$_REQUEST['next_year']] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			'._('Next Year').'
			</TD><TD>
			<SELECT name="next_year">';

			foreach ( $next_year_options as $id => $option )
				$extra['search'] .= '<OPTION value="' . $id . '"">' . $option . '</OPTION>';

			$extra['search'] .= '</SELECT></TD></TR>';

		break;

		// Calendar (Enrollment) Widget
		case 'calendar':

			if ( !$RosarioModules['Students'] )
				break;

			$calendars_RET = DBGet( DBQuery( "SELECT CALENDAR_ID,TITLE
				FROM ATTENDANCE_CALENDARS
				WHERE SYEAR='" . UserSyear() . "'
				AND SCHOOL_ID='" . UserSchool() . "'
				ORDER BY DEFAULT_CALENDAR ASC" ), array(), array( 'CALENDAR_ID' ) );

			if ( $_REQUEST['calendar'] )
			{
				if ( $_REQUEST['calendar'] == '!' )
				{
					$where_not = ($_REQUEST['calendar_not'] == 'Y' ? 'NOT ' : '' );

					$extra['WHERE'] .= " AND ssm.CALENDAR_ID IS " . $where_not . "NULL";

					$text_not = ( $_REQUEST['calendar_not'] == 'Y' ? _( 'Any Value' ) : _( 'No Value' ) );
				}
				else
				{

					$where_not = ($_REQUEST['calendar_not'] == 'Y' ? '!' : '' );

					$extra['WHERE'] .= " AND ssm.CALENDAR_ID" . $where_not . "='" . $_REQUEST['calendar'] . "'";

					$text_not = ( $_REQUEST['calendar_not'] == 'Y' ? _( 'Not' ) . ' ' : '' ) .
						$calendars_RET[$_REQUEST['calendar']][1]['TITLE'];
				}

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Calendar' ) . ': </b>' . $text_not . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Calendar' ) . '
			</TD><TD>
			<label>
				<INPUT type="checkbox" name="calendar_not" value="Y" /> ' . _( 'Not' ) .
			'</label>
			<SELECT name="calendar">
				<OPTION value="">' . _( 'N/A' ) . '</OPTION>
				<OPTION value="!">' . _( 'No Value' ) . '</OPTION>';

			foreach( (array)$calendars_RET as $id => $calendar )
				$extra['search'] .= '<OPTION value="' . $id . '">' . $calendar[1]['TITLE'] . '</OPTION>';

			$extra['search'] .= '</SELECT></TD></TR>';

		break;

		// Attendance Start / Enrolled Widget
		case 'enrolled':

			if ( !$RosarioModules['Students'] )
				break;

			// Verify enrolled begin date
			if ( $_REQUEST['month_enrolled_begin']
				&& $_REQUEST['day_enrolled_begin']
				&& $_REQUEST['year_enrolled_begin'] )
			{
				$_REQUEST['enrolled_begin'] = $_REQUEST['day_enrolled_begin'] . '-' .
					$_REQUEST['month_enrolled_begin'] . '-' .
					$_REQUEST['year_enrolled_begin'];

				if ( !VerifyDate( $_REQUEST['enrolled_begin'] ) )
					unset($_REQUEST['enrolled_begin']);
			}

			// Verify enrolled end date
			if ( $_REQUEST['month_enrolled_end']
				&& $_REQUEST['day_enrolled_end']
				&& $_REQUEST['year_enrolled_end'] )
			{
				$_REQUEST['enrolled_end'] = $_REQUEST['day_enrolled_end'] . '-' .
					$_REQUEST['month_enrolled_end'] . '-' .
					$_REQUEST['year_enrolled_end'];

				if ( !VerifyDate( $_REQUEST['enrolled_end'] ) )
					unset( $_REQUEST['enrolled_end'] );
			}

			if ( $_REQUEST['enrolled_begin']
				&& $_REQUEST['enrolled_end'] )
			{
				$extra['WHERE'] .= " AND ssm.START_DATE
					BETWEEN '" . $_REQUEST['enrolled_begin'] .
					"' AND '" . $_REQUEST['enrolled_end'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Enrolled' ) . ' ' . _( 'Between' ) . ': </b>' .
						ProperDate( $_REQUEST['enrolled_begin'] ) . ' &amp; ' .
						ProperDate( $_REQUEST['enrolled_end'] ) . '<BR />';
			}
			elseif ( $_REQUEST['enrolled_begin'] )
			{
				$extra['WHERE'] .= " AND ssm.START_DATE>='" . $_REQUEST['enrolled_begin'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Enrolled' ) . ' ' . _( 'On or After' ) . ': </b>' .
						ProperDate( $_REQUEST['enrolled_begin'] ) . '<BR />';
			}
			elseif ( $_REQUEST['enrolled_end'] )
			{
				$extra['WHERE'] .= " AND ssm.START_DATE<='" . $_REQUEST['enrolled_end'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Enrolled' ) . ' ' . _( 'On or Before' ) . ': </b>' .
						ProperDate( $_REQUEST['enrolled_end'] ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Attendance Start' ) . '
			</TD><TD>
			<table class="cellspacing-0"><tr><td class="sizep2">
			&ge;
			</td><td>
			' . PrepareDate( '', '_enrolled_begin', true, array( 'short' => true ) ) . '
			</td></tr><tr><td class="sizep2">
			&le;
			</td><td>
			' . PrepareDate( '', '_enrolled_end', true, array( 'short' => true ) ) . '
			</td></tr></table>
			</TD></TR>';

		break;

		// Previously Enrolled Widget
		case 'rolled':

			if ( !$RosarioModules['Students'] )
				break;

			if ( $_REQUEST['rolled'] )
			{
				$extra['WHERE'] .= " AND " . ( $_REQUEST['rolled'] == 'Y' ? '' : 'NOT ' ) . "exists
					(SELECT ''
						FROM STUDENT_ENROLLMENT
						WHERE STUDENT_ID=ssm.STUDENT_ID
						AND SYEAR<ssm.SYEAR)";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Previously Enrolled' ) . ': </b>' .
						( $_REQUEST['rolled'] == 'Y' ? _( 'Yes' ) : _( 'No' ) ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Previously Enrolled' ) . '
			</TD><TD>
			<label>
				<INPUT type="radio" value="" name="rolled" checked />&nbsp;' . _( 'N/A' ) .
			'</label>&nbsp; 
			<label>
				<INPUT type="radio" value="Y" name="rolled" />&nbsp;' . _( 'Yes' ) .
			'</label>&nbsp; 
			<label>
				<INPUT type="radio" value="N" name="rolled" />&nbsp;' . _( 'No' ) .
			'</label>
			</TD></TR>';

		break;

		// Food Service Balance Warning Widget
		case 'fsa_balance_warning':
			$value = $GLOBALS['warning'];
			$item = 'fsa_balance';

		// Food Service Balance Widget
		case 'fsa_balance':

			if ( !$RosarioModules['Food_Service'] )
				break;

			if ( is_numeric( $_REQUEST['fsa_balance'] ) )
			{
				if ( !mb_strpos( $extra['FROM'], 'fssa' ) )
				{
					$extra['FROM'] .= ',FOOD_SERVICE_STUDENT_ACCOUNTS fssa';

					$extra['WHERE'] .= ' AND fssa.STUDENT_ID=s.STUDENT_ID';
				}

				$extra['FROM'] .= ",FOOD_SERVICE_ACCOUNTS fsa";
				$extra['WHERE'] .= " AND fsa.ACCOUNT_ID=fssa.ACCOUNT_ID
					AND fsa.BALANCE" . ( $_REQUEST['fsa_bal_ge'] == 'Y' ? '>=' : '<' ) .
					"'" . round(  $_REQUEST['fsa_balance'], 2 ) . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Food Service Balance' ) . ': </b> ' .
						'<span class="sizep2">' . ($_REQUEST['fsa_bal_ge'] == 'Y' ? '&ge;' : '&lt;' ) . '</span>' .
						number_format( $_REQUEST['fsa_balance'], 2 ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Balance' ) . '
			</TD><TD>
			<table class="cellspacing-0"><tr><td>
			<label class="sizep2">&lt; <INPUT type="radio" name="fsa_bal_ge" value="" checked /></label>
			</td><td rowspan="2">
			<INPUT type="text" name="fsa_balance" size="10"' . ( $value ? ' value="' . $value . '"' : '' ) . ' />
			</td></tr><tr><td>
			<label class="sizep2">&ge; <INPUT type="radio" name="fsa_bal_ge" value="Y" /></label>
			</td></tr></table>
			</TD></TR>';

		break;

		// Food Service Discount Widget
		case 'fsa_discount':

			if ( !$RosarioModules['Food_Service'] )
				break;

			if ( $_REQUEST['fsa_discount'] )
			{
				if ( !mb_strpos($extra['FROM'], 'fssa' ) )
				{
					$extra['FROM'] .= ",FOOD_SERVICE_STUDENT_ACCOUNTS fssa";

					$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
				}

				if ( $_REQUEST['fsa_discount'] == 'Full' )
					$extra['WHERE'] .= " AND fssa.DISCOUNT IS NULL";
				else
					$extra['WHERE'] .= " AND fssa.DISCOUNT='" . $_REQUEST['fsa_discount'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Food Service Discount' ) . ': </b>' .
						_( $_REQUEST['fsa_discount'] ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Discount' ) . '
			</TD><TD>
			<SELECT name="fsa_discount">
			<OPTION value="">' . _( 'Not Specified' ) . '</OPTION>
			<OPTION value="Full">' . _( 'Full' ) . '</OPTION>
			<OPTION value="Reduced">' . _( 'Reduced' ) . '</OPTION>
			<OPTION value="Free">' . _( 'Free' ) . '</OPTION>
			</SELECT>
			</TD></TR>';

		break;

		// Food Service Active Account Status Widget
		case 'fsa_status_active':

			$value = 'active';
			$item = 'fsa_status';

		// Food Service Account Status Widget
		case 'fsa_status':

			if ( !$RosarioModules['Food_Service'] )
				break;

			if ( $_REQUEST['fsa_status'] )
			{
				if ( !mb_strpos( $extra['FROM'], 'fssa' ) )
				{
					$extra['FROM'] .= ",FOOD_SERVICE_STUDENT_ACCOUNTS fssa";

					$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
				}

				if ( $_REQUEST['fsa_status'] == 'Active' )
					$extra['WHERE'] .= " AND fssa.STATUS IS NULL";
				else
					$extra['WHERE'] .= " AND fssa.STATUS='" . $_REQUEST['fsa_status'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Account Status' ) . ': </b>' .
						_( $_REQUEST['fsa_status'] ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Account Status' ) . '
			</TD><TD>
			<SELECT name="fsa_status">
			<OPTION value="">' . _( 'Not Specified' ) . '</OPTION>
			<OPTION value="Active"' . ( $value == 'active' ? ' SELECTED' : '' ) . '>' . _( 'Active' ) . '</OPTION>
			<OPTION value="Inactive">' . _( 'Inactive' ) . '</OPTION>
			<OPTION value="Disabled">' . _( 'Disabled' ) . '</OPTION>
			<OPTION value="Closed">' . _( 'Closed' ) . '</OPTION>
			</SELECT>
			</TD></TR>';

		break;

		// Food Service Barcode Widget
		case 'fsa_barcode':

			if ( !$RosarioModules['Food_Service'] )
				break;

			if ( $_REQUEST['fsa_barcode'] )
			{
				if ( !mb_strpos( $extra['FROM'], 'fssa' ) )
				{
					$extra['FROM'] .= ",FOOD_SERVICE_STUDENT_ACCOUNTS fssa";

					$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
				}

				$extra['WHERE'] .= " AND fssa.BARCODE='" . $_REQUEST['fsa_barcode'] . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Food Service Barcode' ) . ': </b>' .
						$_REQUEST['fsa_barcode'] . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Barcode' ) . '
			</TD><TD>
			<INPUT type="text" name="fsa_barcode" size="15" maxlength="50" />
			</TD></TR>';

		break;

		// Food Service Account ID Widget
		case 'fsa_account_id':

			if ( !$RosarioModules['Food_Service'] )
				break;

			if ( is_numeric( $_REQUEST['fsa_account_id'] ) )
			{
				if ( !mb_strpos( $extra['FROM'], 'fssa' ) )
				{
					$extra['FROM'] .= ",FOOD_SERVICE_STUDENT_ACCOUNTS fssa";

					$extra['WHERE'] .= " AND fssa.STUDENT_ID=s.STUDENT_ID";
				}

				$extra['WHERE'] .= " AND fssa.ACCOUNT_ID='" . ( $_REQUEST['fsa_account_id'] + 0 ) . "'";

				if ( !$extra['NoSearchTerms'] )
					$_ROSARIO['SearchTerms'] .= '<b>' . _( 'Food Service Account ID' ) . ': </b>' .
						( $_REQUEST['fsa_account_id'] + 0 ) . '<BR />';
			}

			$extra['search'] .= '<TR class="st"><TD>
			' . _( 'Account ID' ) . '
			</TD><TD>
			<INPUT type="text" name="fsa_account_id" size="3" maxlength="10" />
			</TD></TR>';

		break;
	}

	$_ROSARIO['Widgets'][$item] = true;

	return true;
}
