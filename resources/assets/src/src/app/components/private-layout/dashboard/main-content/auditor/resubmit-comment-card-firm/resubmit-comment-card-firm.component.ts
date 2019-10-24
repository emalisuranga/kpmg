import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { AuditorDataService } from '../auditor-data.service';

@Component({
  selector: 'app-resubmit-comment-card-firm',
  templateUrl: './resubmit-comment-card-firm.component.html',
  styleUrls: ['./resubmit-comment-card-firm.component.scss']
})
export class ResubmitCommentCardFirmComponent implements OnInit {

  comments = [];
  firmId: number;

  constructor(private router: Router,
    private auditorService: AuditorService,
    private AudData: AuditorDataService,
    private route: ActivatedRoute, ) {

    this.firmId = this.AudData.getFirmId;
    if ((this.firmId === undefined)) {
      this.firmId = parseInt(localStorage.getItem('firmId'), 10);
    }

    if (!(this.firmId === undefined)) {
      localStorage.setItem('firmId', this.firmId.toString());
      this.loadComments(this.firmId);
    }
  }

  ngOnInit() {
  }

  loadComments(audId) {
    const data = {
      audId: audId,
      type: 'firm',
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

  continueResubmition(firmId = this.firmId) {
    if (firmId) {
      this.router.navigate(['dashboard/selectregisterauditor/resubmitauditorfirm']);
      this.AudData.setFirmId(firmId);
      this.firmId = undefined;
    }
  }
}


