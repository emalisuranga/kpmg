import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-auditor-firm-card',
  templateUrl: './auditor-firm-card.component.html',
  styleUrls: ['./auditor-firm-card.component.scss']
})
export class AuditorFirmCardComponent implements OnInit {
  auditorId: string;
  name: any;
  private sub: any;
  id: number;
  auditorfirmchangedetails = [];

  constructor(private route: ActivatedRoute,
    private spinner: NgxSpinnerService,
    private router: Router,
    private auditorService: AuditorService ) {
      this.auditorId = route.snapshot.paramMap.get('id');
     }

  ngOnInit() {
    this.spinner.show();
    this.sub = this.route.params.subscribe(params => {
      this.auditorService.auditorFirmCardLoad(params['id'])
        .subscribe(
          req => {
            this.name = req['auditorfirminfo'];
            this.id = params['id'];
            // this.comments = req['comments'];
            this.auditorfirmchangedetails = req['auditorfirmchangedetails'];
          },
          error => {
            console.log(error);
          },
          () => {
            this.spinner.hide();
           // console.log(error);
          }


        );
    });
  }

  audChange() {
    this.router.navigate(['dashboard/auditorfirmchange', this.auditorId]);
  }

  continueRegistrationForaudChange() {
    if (this.auditorfirmchangedetails[0]['setKey'] === 'AUDITOR_CHANGE_REQUEST_TO_RESUBMIT') {
      this.router.navigate(['/dashboard/auditorfirmchange', this.auditorId]);
    }
  }

}
