import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-auditor-ind-card',
  templateUrl: './auditor-ind-card.component.html',
  styleUrls: ['./auditor-ind-card.component.scss']
})
export class AuditorIndCardComponent implements OnInit {
  auditorId: string;
  name: any;
  private sub: any;
  id: number;
  auditorchangedetails = [];

  constructor(private route: ActivatedRoute,
    private spinner: NgxSpinnerService,
    private router: Router,
    private auditorService: AuditorService ) {
    this.auditorId = route.snapshot.paramMap.get('id');
   }

  ngOnInit() {
    this.spinner.show();
    this.sub = this.route.params.subscribe(params => {
      this.auditorService.auditorIndCardLoad(params['id'])
        .subscribe(
          req => {
            this.name = req['auditorinfo'];
            this.id = params['id'];
            // this.comments = req['comments'];
            this.auditorchangedetails = req['auditorchangedetails'];
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
    this.router.navigate(['dashboard/auditorchange', this.auditorId]);
  }

  continueRegistrationForaudChange() {
    if (this.auditorchangedetails[0]['setKey'] === 'AUDITOR_CHANGE_REQUEST_TO_RESUBMIT') {
      this.router.navigate(['/dashboard/auditorchange', this.auditorId]);
    }
  }

}
