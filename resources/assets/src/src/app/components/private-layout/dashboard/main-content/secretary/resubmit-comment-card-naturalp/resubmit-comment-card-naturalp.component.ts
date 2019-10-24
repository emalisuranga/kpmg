import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { SecretaryDataService } from '../secretary-data.service';

@Component({
  selector: 'app-resubmit-comment-card-naturalp',
  templateUrl: './resubmit-comment-card-naturalp.component.html',
  styleUrls: ['./resubmit-comment-card-naturalp.component.scss']
})
export class ResubmitCommentCardNaturalpComponent implements OnInit {

  comments = [];
  secId: number;
  nic: string;

  constructor(
    private route: ActivatedRoute,
    private secretaryService: SecretaryService,
    private router: Router,
    private SecData: SecretaryDataService
  ) {

    this.secId = this.SecData.getSecId;
    if ((this.secId === undefined)) {
      this.secId = parseInt(localStorage.getItem('secId'), 10);
    }
    this.nic = this.SecData.getNic;
    if ((this.nic === undefined)) {
      this.nic = localStorage.getItem('nic');
    }


    if (!(this.secId === undefined)) {
      localStorage.setItem('secId', this.secId.toString());
      this.loadComments(this.secId);
      this.SecData.secId = undefined;
    }
    if (!(this.nic === undefined)) {
      localStorage.setItem('nic', this.nic);
    }
  }

  ngOnInit() {
  }

  loadComments(secId) {
    const data = {
      secId: secId,
      type: 'individual',
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

  continueResubmition(nic = this.nic, secId = this.secId) {

    if (nic) {
      this.router.navigate(['dashboard/selectregistersecretary/resubmitsecretarynatural', nic]);
      this.SecData.setSecId(secId);
      this.SecData.nic = undefined;
      this.nic = undefined;
    }
  }
}
