import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { GlobleUserService } from '../../../../../http/shared/globle.user.service';
import { DataService } from '../../../../../storage/data.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-reservation',
  templateUrl: './reservation.component.html',
  styleUrls: ['./reservation.component.scss']
})
export class ReservationComponent implements OnInit {

  name: string;
  postfixname: string;
  companyType: number;
  applicantName: string;
  oldnumber: string;

  constructor(
    private route: Router,
    public user: GlobleUserService,
    private data: DataService,
    private snotifyService: ToastrService) { }

  ngOnInit() {
    if (this.data.isLocalData('ResName')) {
      this.data.outLocalData('ResName');
      this.route.navigate(['/']);
      this.snotifyService.error('Your request has failed. Please try again', 'Error');
    } else {
      this.name = this.data.getLocalData('ResName')['name'];
      this.postfixname = this.data.getLocalData('ResName')['postfix'];
      this.companyType = this.data.getLocalData('ResName')['comType'];
      this.oldnumber = this.data.getLocalData('ResName')['oldnumber'];
    }
  }
}
