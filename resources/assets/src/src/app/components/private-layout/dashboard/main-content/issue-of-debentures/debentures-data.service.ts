import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DebenturesDataService {

  comId: number;
  status: string;
  reqId: number;
  email: string;
  routetoDashboard: boolean = false;

  constructor() { }

  // for continue upload process after some reasons...
  setComId(comId: number) {
    this.comId = comId;
  }

  get getComId() {
    return this.comId;
  }

  setStatus(status: string) {
    this.status = status;
  }

  get getStatus() {
    return this.status;
  }

  setEmail(email: string) {
    this.email = email;
  }

  get getEmail() {
    return this.email;
  }

  setReqId(reqId: number) {
    this.reqId = reqId;
  }

  get getReqId() {
    return this.reqId;
  }

  setNavigatetoDashboard(routetoDashboard: boolean) {
    this.routetoDashboard = routetoDashboard;
  }

  get getNavigatetoDashboard() {
    return this.routetoDashboard;
  }
}
