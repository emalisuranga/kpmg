import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { AuditorDataService } from '../auditor-data.service';

@Component({
  selector: 'app-resubmit-comment-card-sl',
  templateUrl: './resubmit-comment-card-sl.component.html',
  styleUrls: ['./resubmit-comment-card-sl.component.scss']
})
export class ResubmitCommentCardSlComponent implements OnInit {

  comments = [];
  audId: number;
  nic: string;

  constructor(private router: Router,
    private auditorService: AuditorService,
    private AudData: AuditorDataService,
    private route: ActivatedRoute, ) {

    this.audId = this.AudData.getAudId;
    if ((this.audId === undefined)) {
      this.audId = parseInt(localStorage.getItem('audId'), 10);
    }
    this.nic = this.AudData.getNic;
    if ((this.nic === undefined)) {
      this.nic = localStorage.getItem('nic');
    }


    if (!(this.audId === undefined)) {
      localStorage.setItem('audId', this.audId.toString());
      this.loadComments(this.audId);
      this.AudData.audId = undefined;
    }
    if (!(this.nic === undefined)) {
      localStorage.setItem('nic', this.nic);
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

  continueResubmition(nic = this.nic, audId = this.audId) {

    if (nic) {
      this.router.navigate(['dashboard/selectregisterauditor/resubmitauditornaturalsl', nic]);
      this.AudData.setAudId(audId);
      this.AudData.nic = undefined;
      this.nic = undefined;
    }
  }

}
