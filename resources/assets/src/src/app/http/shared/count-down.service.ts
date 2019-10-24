import { OnInit, OnDestroy, Injectable } from '@angular/core';
import { Observable, Subscription } from 'rxjs';
import { interval } from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class CountDownService implements OnDestroy {

  private future: Date;
  private counter$: Observable<number>;
  private subscription: Subscription;
  private message: string;

  constructor() { }

  dhms(t) {
    let days, hours, minutes, seconds;
    days = Math.floor(t / 86400);
    t -= days * 86400;
    hours = Math.floor(t / 3600) % 24;
    t -= hours * 3600;
    minutes = Math.floor(t / 60) % 60;
    t -= minutes * 60;
    seconds = t % 60;

    return [
      days + 'd',
      hours + 'h',
      minutes + 'm',
      seconds + 's'
    ].join(' ');
  }

  countDown(date: string, days: number = 30): string {
    this.future = new Date(date);
    this.future.setDate(this.future.getDate() + days);
    this.counter$ = interval(1000).pipe(
      map((x) => {
        return Math.floor((this.future.getTime() - new Date().getTime()) / 1000);
      }));
    this.subscription = this.counter$.subscribe((x) => this.message = this.dhms(x));
    return this.message;
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
