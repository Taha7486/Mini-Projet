<?php

class AttestationPdfService {
	private $dompdfAvailable;

	public function __construct() {
		$this->dompdfAvailable = class_exists('Dompdf\\Dompdf');
	}

	public function generateForRegistration($db, $registrationId, $event, $participant, $organizerName) {
		if (!$this->dompdfAvailable) {
			return null; // Dompdf not installed yet; caller can handle fallback
		}

		$html = $this->renderHtml($event, $participant, $organizerName, $registrationId);

		$dompdf = new Dompdf\Dompdf();
		$dompdf->loadHtml($html, 'UTF-8');
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();

		$baseDir = __DIR__ . '/../storage/attestations/' . intval($event['event_id']);
		if (!is_dir($baseDir)) {
			@mkdir($baseDir, 0775, true);
		}

		$filename = $baseDir . '/' . intval($registrationId) . '-' . $this->slugify($participant['nom'] ?? 'participant') . '.pdf';
		file_put_contents($filename, $dompdf->output());

		return realpath($filename) ?: $filename;
	}

	private function renderHtml($event, $participant, $organizerName, $registrationId) {
		$title = htmlspecialchars($event['title']);
		$date = htmlspecialchars($event['date_event']);
		$time = date('g:i A', strtotime($event['start_time'])) . ' - ' . date('g:i A', strtotime($event['end_time']));
		$location = htmlspecialchars($event['location']);
		$name = htmlspecialchars($participant['nom'] ?? '');
		$issueDate = date('Y-m-d');

		return '<!doctype html><html><head><meta charset="utf-8"><style>
			body{font-family: DejaVu Sans, Arial, sans-serif; color:#0b1b3b;}
			.wrapper{padding:24px;background:#0b1b3b;}
			.certificate{background:#ffffff;border:10px solid #0b1b3b;padding:48px;border-radius:8px;}
			.header{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
			.title{font-size:34px;font-weight:700;margin:0;color:#0b1b3b;text-transform:uppercase;letter-spacing:2px}
			.subtitle{font-size:14px;margin:8px 0 32px;color:#2a3e6b}
			.name{font-size:30px;font-weight:700;margin:8px 0 16px;color:#0b1b3b;text-align:center}
			.line{height:2px;background:#0b1b3b;width:160px;margin:16px auto;border-radius:1px}
			.block{font-size:16px;color:#2a3e6b;text-align:center;margin:8px 0}
			.event{font-size:22px;font-weight:700;color:#0b1b3b;text-align:center;margin:8px 0 16px}
			.meta{font-size:14px;color:#2a3e6b;text-align:center;margin:6px 0}
			.footer{display:flex;align-items:flex-end;justify-content:center;margin-top:56px}
			.footerInner{display:grid;grid-template-columns:repeat(2, 260px);justify-content:center;gap:72px}
			.sig{text-align:center;color:#0b1b3b}
			.sigLine{height:2px;background:#0b1b3b;width:220px;margin:0 auto 8px;border-radius:1px}
			.sigName{font-size:12px;font-weight:700;margin:2px 0}
			.sigRole{font-size:11px;color:#2a3e6b}
		</style></head><body>
			<div class="wrapper">
				<div class="certificate">
					<div class="header">
						<div class="title">Certificate of Attendance</div>
						<div class="subtitle">This is to certify that</div>
						<div class="name">' . $name . '</div>
						<div class="line"></div>
						<div class="block">has attended the event</div>
						<div class="event">' . $title . '</div>
						<div class="meta">Date: ' . $date . ' • Time: ' . $time . '</div>
						<div class="meta">Location: ' . $location . '</div>
						<div class="meta">Issued: ' . $issueDate . '</div>
					</div>
					<div class="footer">
						<div class="footerInner">
							<div class="sig">
								<div class="sigLine"></div>
								<div class="sigName">' . htmlspecialchars($organizerName) . '</div>
								<div class="sigRole">Organizer</div>
							</div>
							<div class="sig">
								<div class="sigLine"></div>
								<div class="sigName">Campus Events</div>
								<div class="sigRole">Authorized Signature</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</body></html>';
	}

	private function slugify($text) {
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = trim($text, '-');
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = strtolower($text);
		$text = preg_replace('~[^-a-z0-9]+~', '', $text);
		if (empty($text)) { return 'file'; }
		return $text;
	}
}

?>

