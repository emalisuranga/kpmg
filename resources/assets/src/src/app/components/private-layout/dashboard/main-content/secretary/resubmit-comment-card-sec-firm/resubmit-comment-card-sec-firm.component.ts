import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { SecretaryDataService } from '../secretary-data.service';

@Component({
  selector: 'app-resubmit-comment-card-sec-firm',
  templateUrl: './resubmit-comment-card-sec-firm.component.html',
  styleUrls: ['./resubmit-comment-card-sec-firm.component.scss']
})
export class ResubmitCommentCardSecFirmComponent implements OnInit {

  comments = [];
  firmId: number;
  nic: string;

  constructor(
    private route: ActivatedRoute,
    private secretaryService: SecretaryService,
    private router: Router,
    private SecData: SecretaryDataService
  ) {

    this.firmId = this.SecData.getFirmId;
    if ((this.firmId === undefined)) {
      this.firmId = parseInt(localStorage.getItem('firmId'), 10);
    }

    if (!(this.firmId === undefined)) {
      localStorage.setItem('firmId', this.firmId.toString());
      this.loadComments(this.firmId);
      this.SecData.firmId = undefined;
    }

  }

  ngOnInit() {
  }

  loadComments(firmId) {
    const data = {
      secId: firmId,
      type: 'firm',
    };
    this.secretaryService.secretaryCommentsLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['secretaryComment']) {
              for (let i in req['data']['secretaryComment']) {
                const data1 = {
                  id: req['data']['secretaryComment'][i]['id'],
                  comment: req['data']['secretaryComment'][i]['comments'],
                  createdAt: req['data']['secretaryComment'][i]['created_at'],
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
      this.router.navigate(['dashboard/selectregistersecretary/resubmitsecretaryfirm']);
      this.SecData.setFirmId(firmId);
      this.firmId = undefined;
    }
  }

}
