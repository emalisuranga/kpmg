import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { AuditorDataService } from '../auditor-data.service';


@Component({
  selector: 'app-resubmit-comment-card-nonsl',
  templateUrl: './resubmit-comment-card-nonsl.component.html',
  styleUrls: ['./resubmit-comment-card-nonsl.component.scss']
})
export class ResubmitCommentCardNonslComponent implements OnInit {

  comments = [];
  audId: number;
  passport: string;

  constructor(private router: Router,
    private auditorService: AuditorService,
    private AudData: AuditorDataService,
    private route: ActivatedRoute, ) {

    this.audId = this.AudData.getAudId;
    if ((this.audId === undefined)) {
      this.audId = parseInt(localStorage.getItem('audId'), 10);
    }
    this.passport = this.AudData.getPassport;
    if ((this.passport === undefined)) {
      this.passport = localStorage.getItem('passport');
    }


    if (!(this.audId === undefined)) {
      localStorage.setItem('audId', this.audId.toString());
      this.loadComments(this.audId);
      this.AudData.audId = undefined;
    }
    if (!(this.passport === undefined)) {
      localStorage.setItem('passport', this.passport);
    }

  }

  ngOnInit() {

  }


  loadComments(audId) {
    const data = {
      audId: audId,
      type: 'individual',
    };
    this.auditorService.auditorCommentsLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['auditorComment']) {
              for (let i in req['data']['auditorComment']) {
                const data1 = {
                  id: req['data']['auditorComment'][i]['id'],
                  comment: req['data']['auditorComment'][i]['comments'],
                  createdAt: req['data']['auditorComment'][i]['created_at'],
                };
                this.comments.push(data1);
              }
            }
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  continueResubmition(passport = this.passport, audId = this.audId) {

    if (passport) {
      this.router.navigate(['dashboard/selectregisterauditor/resubmitauditornaturalnonsl', passport]);
      this.AudData.setAudId(audId);
      this.AudData.passport = undefined;
      this.passport = undefined;
    }
  }

}
