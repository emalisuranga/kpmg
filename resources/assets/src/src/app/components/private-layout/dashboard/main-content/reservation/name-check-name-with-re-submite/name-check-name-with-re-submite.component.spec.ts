import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NameCheckNameWithReSubmiteComponent } from './name-check-name-with-re-submite.component';

describe('NameCheckNameWithReSubmiteComponent', () => {
  let component: NameCheckNameWithReSubmiteComponent;
  let fixture: ComponentFixture<NameCheckNameWithReSubmiteComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NameCheckNameWithReSubmiteComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NameCheckNameWithReSubmiteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
